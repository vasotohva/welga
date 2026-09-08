#!/usr/bin/env python3
"""Build normalized WELGA product media/document migration package.

Reads legacy product manifest + source ZIPs directly, validates physical assets,
rejects broken/model-mismatched PDF associations, computes SHA-256 checksums and
emits deterministic SQL + JSON. Optionally copies production assets without
using platform legacy ZIP extraction.
"""
from __future__ import annotations

import argparse
import hashlib
import io
import json
import mimetypes
import os
import re
import unicodedata
import zipfile
from collections import defaultdict
from pathlib import Path

try:
    from PIL import Image
except ImportError:
    Image = None


def sqlq(v):
    if v is None:
        return "NULL"
    if isinstance(v, (int, float)):
        return str(v)
    s = str(v).replace("\\", "\\\\").replace("'", "\\'").replace("\x00", "")
    return "'" + s + "'"


def canonical_model(product):
    raw = str(product.get("model") or "").strip()
    bg = (product.get("legacy_names") or {}).get("bg") or ""
    match = re.search(r"(?:^|\b)W[\s_-]*(\d{2,4})(?:\b|$)", raw, re.I)
    if not match:
        match = re.fullmatch(r"0*(\d{2,4})", raw)
    if match:
        return f"W-{int(match.group(1))}"
    numbers = re.findall(r"(?<!\d)(\d{2,4})(?!\d)", bg)
    if numbers:
        return f"W-{int(numbers[-1])}"
    slug = re.sub(r"[^A-Za-z0-9]+", "-", raw).strip("-") or "UNKNOWN"
    return f"W-{slug.upper()}"


def model_number(model):
    match = re.fullmatch(r"W-(\d+)", model, re.I)
    return int(match.group(1)) if match else None


def safe_ascii_slug(value, fallback="file"):
    value = unicodedata.normalize("NFKD", str(value)).encode("ascii", "ignore").decode("ascii").lower()
    value = re.sub(r"[^a-z0-9]+", "-", value).strip("-")
    return value or fallback


def source_key_from_legacy(path):
    path = path.replace("\\", "/").lstrip("/")
    if path.startswith("image/"):
        path = path[6:]
    if path.startswith("data/"):
        return "data", path[5:]
    if path.startswith("photos/"):
        return "photos", path
    if path.startswith("video/"):
        return "video", path
    return "data", path


class Sources:
    def __init__(self, data_zips, photos_zip=None, video_zip=None):
        self.handles = []
        self.index = {}
        for zip_path in data_zips:
            zf = zipfile.ZipFile(zip_path)
            self.handles.append(zf)
            for info in zf.infolist():
                if info.is_dir():
                    continue
                key = ("data", info.filename)
                if key in self.index:
                    raise RuntimeError(f"duplicate source entry: {key}")
                self.index[key] = (zf, info)
        for namespace, zip_path in (("photos", photos_zip), ("video", video_zip)):
            if not zip_path:
                continue
            zf = zipfile.ZipFile(zip_path)
            self.handles.append(zf)
            for info in zf.infolist():
                if not info.is_dir():
                    self.index[(namespace, info.filename)] = (zf, info)

    def read(self, legacy_path):
        item = self.index.get(source_key_from_legacy(legacy_path))
        if not item:
            return None
        zf, info = item
        return zf.read(info)

    def close(self):
        for handle in self.handles:
            handle.close()


def doc_folder_model(path):
    match = re.search(r"(?:^|/)Models/(\d+)(?:/|$)", path, re.I)
    return int(match.group(1)) if match else None


def doc_version(path, role):
    basename = os.path.splitext(os.path.basename(path))[0]
    if role == "price_list":
        match = re.search(r"price[_-]?(\d+[a-z]?)", basename, re.I)
        return match.group(1) if match else None
    match = re.search(r"[_-]V(\d{1,2}[-_]\d{2,4})", basename, re.I)
    if match:
        return "V" + match.group(1).replace("_", "-")
    match = re.search(r"[_-](\d{1,2}[-_]\d{2,4})$", basename)
    return match.group(1).replace("_", "-") if match else None


def normalized_image_path(model, role, order, original, digest):
    ext = os.path.splitext(original)[1].lower() or ".jpg"
    label = "main" if role == "primary" else f"gallery-{order:03d}"
    return f"media/products/{model.lower()}/images/{label}-{digest[:10]}{ext}"


def normalized_document_path(model, role, version, original, digest):
    ext = os.path.splitext(original)[1].lower() or ".pdf"
    label = "price-list" if role == "price_list" else "technical"
    if version:
        label += "-" + safe_ascii_slug(version, "version")
    return f"media/products/{model.lower()}/documents/{label}-{digest[:10]}{ext}"


def write_asset(root, relative_path, data):
    destination = Path(root) / relative_path
    destination.parent.mkdir(parents=True, exist_ok=True)
    if destination.exists():
        old_hash = hashlib.sha256(destination.read_bytes()).digest()
        new_hash = hashlib.sha256(data).digest()
        if old_hash != new_hash:
            raise RuntimeError(f"production path collision: {relative_path}")
        return
    destination.write_bytes(data)


def image_dimensions(blob):
    if Image is None:
        return None, None
    try:
        with Image.open(io.BytesIO(blob)) as image:
            return image.size
    except Exception:
        return None, None


def review_key(item):
    raw = "|".join([
        item["entity_type"],
        str(item.get("legacy_entity_id") or ""),
        item["issue_code"],
        str(item.get("source_path") or ""),
    ])
    return hashlib.sha256(raw.encode("utf-8")).hexdigest()


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--manifest", required=True)
    parser.add_argument("--data-zip", action="append", required=True)
    parser.add_argument("--photos-zip")
    parser.add_argument("--video-zip")
    parser.add_argument("--out-dir", required=True)
    parser.add_argument("--copy-root")
    args = parser.parse_args()

    products = json.loads(Path(args.manifest).read_text("utf-8"))
    sources = Sources(args.data_zip, args.photos_zip, args.video_zip)
    out_dir = Path(args.out_dir)
    out_dir.mkdir(parents=True, exist_ok=True)

    assets = []
    reviews = []
    sql = []
    emit = sql.append
    stats = defaultdict(int)
    seen_media_paths = {}
    seen_document_paths = {}

    emit("-- WELGA generated product media/document seed")
    emit("-- Requires migrations 001-006 and the active product seed.")
    emit("SET NAMES utf8mb4;")
    emit("SET FOREIGN_KEY_CHECKS = 0;")
    emit("")

    try:
        for product in sorted(products, key=lambda item: int(item["legacy_product_id"])):
            if int(product.get("status") or 0) != 1:
                continue
            legacy_product_id = int(product["legacy_product_id"])
            model = canonical_model(product)
            numeric_model = model_number(model)

            image_refs = []
            if product.get("main_image"):
                image_refs.append(("primary", 0, product["main_image"]))
            for gallery in product.get("gallery") or []:
                image_refs.append(("gallery", int(gallery.get("sort_order") or 0), gallery["path"]))

            for role, order, legacy_path in image_refs:
                blob = sources.read(legacy_path)
                if blob is None:
                    stats["missing_images"] += 1
                    reviews.append({
                        "entity_type": "product",
                        "legacy_entity_id": str(legacy_product_id),
                        "issue_code": "missing_media",
                        "severity": "warning",
                        "source_path": legacy_path,
                        "message": f"{model}: referenced product image is missing physically",
                    })
                    continue

                digest = hashlib.sha256(blob).hexdigest()
                mime_type = mimetypes.guess_type(legacy_path)[0] or "application/octet-stream"
                width, height = image_dimensions(blob)
                production_path = normalized_image_path(model, role, order, legacy_path, digest)
                if production_path in seen_media_paths and seen_media_paths[production_path] != digest:
                    raise RuntimeError(f"media collision: {production_path}")
                seen_media_paths[production_path] = digest
                if args.copy_root:
                    write_asset(args.copy_root, production_path, blob)

                assets.append({
                    "kind": "product_image",
                    "legacy_product_id": legacy_product_id,
                    "model": model,
                    "role": role,
                    "sort_order": order,
                    "legacy_path": legacy_path,
                    "path": production_path,
                    "sha256": digest,
                    "file_size": len(blob),
                    "mime_type": mime_type,
                    "width": width,
                    "height": height,
                })
                emit(
                    "INSERT INTO media (path, legacy_path, mime_type, width, height, file_size, checksum_sha256, status) VALUES "
                    f"({sqlq(production_path)}, {sqlq(legacy_path)}, {sqlq(mime_type)}, {sqlq(width)}, {sqlq(height)}, {len(blob)}, {sqlq(digest)}, 1) "
                    "ON DUPLICATE KEY UPDATE legacy_path=VALUES(legacy_path), mime_type=VALUES(mime_type), "
                    "width=VALUES(width), height=VALUES(height), file_size=VALUES(file_size), "
                    "checksum_sha256=VALUES(checksum_sha256), status=1;"
                )
                if role == "primary":
                    emit(
                        "UPDATE catalog_products p JOIN media m ON m.path=" + sqlq(production_path) +
                        f" SET p.primary_media_id=m.media_id WHERE p.legacy_product_id={legacy_product_id};"
                    )
                else:
                    emit(
                        "INSERT IGNORE INTO catalog_product_media (product_id, media_id, role, sort_order) "
                        f"SELECT p.product_id, m.media_id, 'gallery', {order} FROM catalog_products p "
                        f"JOIN media m ON m.path={sqlq(production_path)} WHERE p.legacy_product_id={legacy_product_id};"
                    )
                stats["images_imported"] += 1

            seen_legacy_documents = set()
            for document in product.get("documents") or []:
                legacy_path = document.get("path")
                role = document.get("role") or "technical"
                if not legacy_path or legacy_path in seen_legacy_documents:
                    continue
                seen_legacy_documents.add(legacy_path)
                blob = sources.read(legacy_path)

                if blob is None:
                    stats["missing_documents"] += 1
                    reviews.append({
                        "entity_type": "product_document",
                        "legacy_entity_id": str(legacy_product_id),
                        "issue_code": "missing_document",
                        "severity": "warning",
                        "source_path": legacy_path,
                        "message": f"{model}: Journal document reference has no physical source file",
                        "payload": document,
                    })
                    continue

                folder_model = doc_folder_model(legacy_path)
                if folder_model is not None and numeric_model is not None and folder_model != numeric_model:
                    stats["mismatched_documents"] += 1
                    reviews.append({
                        "entity_type": "product_document",
                        "legacy_entity_id": str(legacy_product_id),
                        "issue_code": "document_model_mismatch",
                        "severity": "error",
                        "source_path": legacy_path,
                        "message": f"{model}: PDF belongs to Models/{folder_model}, refusing legacy association",
                        "payload": document,
                    })
                    continue

                digest = hashlib.sha256(blob).hexdigest()
                version = doc_version(legacy_path, role)
                production_path = normalized_document_path(model, role, version, legacy_path, digest)
                if production_path in seen_document_paths and seen_document_paths[production_path] != digest:
                    raise RuntimeError(f"document collision: {production_path}")
                seen_document_paths[production_path] = digest
                if args.copy_root:
                    write_asset(args.copy_root, production_path, blob)

                assets.append({
                    "kind": "product_document",
                    "legacy_product_id": legacy_product_id,
                    "model": model,
                    "role": role,
                    "version": version,
                    "language_hint": document.get("language_hint"),
                    "legacy_path": legacy_path,
                    "path": production_path,
                    "sha256": digest,
                    "file_size": len(blob),
                    "mime_type": "application/pdf",
                    "module_id": document.get("module_id"),
                })

                emit(
                    "INSERT INTO documents (document_type, path, legacy_path, version, legacy_url, mime_type, file_size, checksum_sha256, status) VALUES "
                    f"({sqlq(role)}, {sqlq(production_path)}, {sqlq(legacy_path)}, {sqlq(version)}, "
                    f"{sqlq('/image/' + legacy_path)}, 'application/pdf', {len(blob)}, {sqlq(digest)}, 1) "
                    "ON DUPLICATE KEY UPDATE legacy_path=VALUES(legacy_path), version=VALUES(version), "
                    "legacy_url=VALUES(legacy_url), mime_type=VALUES(mime_type), file_size=VALUES(file_size), "
                    "checksum_sha256=VALUES(checksum_sha256), status=1;"
                )
                titles = {
                    "bg": "Ценова листа" if role == "price_list" else "Техническа информация",
                    "en": "Price list" if role == "price_list" else "Technical information",
                    "de": "Preisliste" if role == "price_list" else "Technische Informationen",
                }
                for language_code, title in titles.items():
                    emit(
                        "INSERT IGNORE INTO document_translations (document_id, language_id, title) "
                        f"SELECT d.document_id, l.language_id, {sqlq(title)} FROM documents d "
                        f"JOIN languages l ON l.code={sqlq(language_code)} WHERE d.path={sqlq(production_path)};"
                    )
                emit(
                    "INSERT IGNORE INTO catalog_product_documents (product_id, document_id, role, sort_order) "
                    f"SELECT p.product_id, d.document_id, {sqlq(role)}, 0 FROM catalog_products p "
                    f"JOIN documents d ON d.path={sqlq(production_path)} WHERE p.legacy_product_id={legacy_product_id};"
                )
                stats["documents_imported"] += 1

        emit("")
        emit("-- Explicit legacy review queue: no silent guessing.")
        for item in reviews:
            payload = item.get("payload")
            emit(
                "INSERT INTO migration_review_queue "
                "(review_key, entity_type, legacy_entity_id, issue_code, severity, source_path, message, payload_json) VALUES "
                f"({sqlq(review_key(item))}, {sqlq(item['entity_type'])}, {sqlq(item.get('legacy_entity_id'))}, "
                f"{sqlq(item['issue_code'])}, {sqlq(item['severity'])}, {sqlq(item.get('source_path'))}, "
                f"{sqlq(item['message'])}, {sqlq(json.dumps(payload, ensure_ascii=False)) if payload is not None else 'NULL'}) "
                "ON DUPLICATE KEY UPDATE severity=VALUES(severity), source_path=VALUES(source_path), "
                "message=VALUES(message), payload_json=VALUES(payload_json);"
            )
        emit("SET FOREIGN_KEY_CHECKS = 1;")
    finally:
        sources.close()

    (out_dir / "product-assets.json").write_text(json.dumps(assets, ensure_ascii=False, indent=2), "utf-8")
    (out_dir / "media-document-review.json").write_text(json.dumps(reviews, ensure_ascii=False, indent=2), "utf-8")
    (out_dir / "102_media_documents_seed.sql").write_text("\n".join(sql) + "\n", "utf-8")

    report = dict(stats)
    report.update({
        "active_products": sum(1 for product in products if int(product.get("status") or 0) == 1),
        "assets": len(assets),
        "review_items": len(reviews),
        "distinct_missing_document_paths": len({
            item.get("source_path") for item in reviews if item.get("issue_code") == "missing_document"
        }),
        "price_lists_imported": sum(
            1 for asset in assets if asset.get("kind") == "product_document" and asset.get("role") == "price_list"
        ),
        "technical_documents_imported": sum(
            1 for asset in assets if asset.get("kind") == "product_document" and asset.get("role") == "technical"
        ),
        "copy_root": args.copy_root,
    })
    (out_dir / "media-document-report.json").write_text(json.dumps(report, ensure_ascii=False, indent=2), "utf-8")
    print(json.dumps(report, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
