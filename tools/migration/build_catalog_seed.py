#!/usr/bin/env python3
"""Build deterministic ETKO catalogue seed SQL from the WELGA legacy dump/manifest.

This generator imports only public catalogue data. It intentionally excludes
customers, orders, credentials and other account data.
"""
from __future__ import annotations

import argparse
import hashlib
import importlib.util
import json
import re
from collections import defaultdict
from pathlib import Path


def load_manifest_helpers(path: Path):
    spec = importlib.util.spec_from_file_location("legacy_manifest", path)
    mod = importlib.util.module_from_spec(spec)
    assert spec.loader
    spec.loader.exec_module(mod)
    return mod


def sqlq(value):
    if value is None:
        return "NULL"
    if isinstance(value, bool):
        return "1" if value else "0"
    if isinstance(value, (int, float)):
        return str(value)
    s = str(value)
    s = s.replace("\\", "\\\\").replace("'", "\\'")
    s = s.replace("\x00", "").replace("\r\n", "\n").replace("\r", "\n")
    return "'" + s + "'"


def strip_tags_and_entities(html: str | None) -> str:
    if not html:
        return ""
    import html as htmlmod
    s = htmlmod.unescape(str(html))
    s = re.sub(r"<script\b[^>]*>.*?</script>", " ", s, flags=re.I | re.S)
    s = re.sub(r"<style\b[^>]*>.*?</style>", " ", s, flags=re.I | re.S)
    s = re.sub(r"<[^>]+>", " ", s)
    return re.sub(r"\s+", " ", s).strip()


def canonical_model(raw_model: str, bg_name: str | None) -> str:
    raw = (raw_model or "").strip()
    m = re.search(r"(?:^|\b)W[\s_-]*(\d{2,4})(?:\b|$)", raw, flags=re.I)
    if not m:
        m = re.fullmatch(r"0*(\d{2,4})", raw)
    if not m and bg_name:
        nums = re.findall(r"(?<!\d)(\d{2,4})(?!\d)", bg_name)
        if nums:
            return f"W-{int(nums[-1])}"
    if m:
        return f"W-{int(m.group(1))}"
    slug = re.sub(r"[^A-Za-z0-9]+", "-", raw).strip("-") or "UNKNOWN"
    return f"W-{slug.upper()}"


def slug_from_alias(keyword: str | None) -> str | None:
    if not keyword:
        return None
    k = str(keyword).strip().strip("/")
    if k.lower().endswith(".html"):
        k = k[:-5]
    return k or None


def load_editorial(directory: Path | None):
    by_pid = {}
    if not directory or not directory.exists():
        return by_pid
    for path in sorted(directory.glob("*.json")):
        try:
            data = json.loads(path.read_text("utf-8"))
        except Exception:
            continue
        if isinstance(data, dict):
            data = [data]
        for item in data:
            if not isinstance(item, dict) or item.get("legacy_product_id") is None:
                continue
            by_pid[int(item["legacy_product_id"])] = item
    return by_pid


def choose_canonical_category(category_ids):
    for cid in (24, 25, 63, 61, 62):
        if cid in category_ids:
            return cid
    return category_ids[0] if category_ids else None


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--sql", required=True)
    ap.add_argument("--manifest", required=True)
    ap.add_argument("--manifest-helper", required=True)
    ap.add_argument("--editorial-dir")
    ap.add_argument("--out", required=True)
    args = ap.parse_args()

    helper = load_manifest_helpers(Path(args.manifest_helper))
    sql = Path(args.sql).read_text("utf-8", errors="replace")
    products_manifest = json.loads(Path(args.manifest).read_text("utf-8"))
    editorial = load_editorial(Path(args.editorial_dir) if args.editorial_dir else None)

    tables = {}
    for t in [
        "oc_product", "oc_product_description", "oc_url_alias",
        "oc_product_to_category", "oc_product_filter",
    ]:
        tables[t] = helper.load_table(sql, t)

    prod_rows = {int(r["product_id"]): r for r in tables["oc_product"]}
    desc = defaultdict(dict)
    for r in tables["oc_product_description"]:
        desc[int(r["product_id"])][int(r["language_id"])] = r
    aliases = defaultdict(dict)
    for r in tables["oc_url_alias"]:
        q = str(r.get("query") or "")
        m = re.fullmatch(r"product_id=(\d+)", q)
        if m:
            aliases[int(m.group(1))][int(r.get("language_id") or 0)] = r.get("keyword")
    cat_rel = defaultdict(list)
    for r in tables["oc_product_to_category"]:
        cat_rel[int(r["product_id"])].append(int(r["category_id"]))
    filter_rel = defaultdict(list)
    for r in tables["oc_product_filter"]:
        filter_rel[int(r["product_id"])].append(int(r["filter_id"]))

    active_pids = sorted(
        [pid for pid, row in prod_rows.items() if int(row.get("status") or 0) == 1],
        key=lambda pid: (str(prod_rows[pid].get("date_added") or ""), pid),
        reverse=True,
    )

    out = []
    w = out.append
    w("-- WELGA / ETKO Catalog CMS")
    w("-- Generated active-catalogue seed from the legacy OpenCart source.")
    w("-- Re-runnable after taxonomy migrations 001-004 and 100.")
    w("SET NAMES utf8mb4;")
    w("SET FOREIGN_KEY_CHECKS = 0;")
    w("SET @bg = (SELECT language_id FROM languages WHERE code='bg' LIMIT 1);")
    w("SET @en = (SELECT language_id FROM languages WHERE code='en' LIMIT 1);")
    w("SET @de = (SELECT language_id FROM languages WHERE code='de' LIMIT 1);")
    w("")

    w("-- Product core")
    for rank, pid in enumerate(active_pids, start=1):
        p = prod_rows[pid]
        bg_name = desc[pid].get(2, {}).get("name")
        model = canonical_model(str(p.get("model") or ""), bg_name)
        canonical_legacy_cat = choose_canonical_category(sorted(cat_rel[pid]))
        content_status = "edited_bg" if pid in editorial else "needs_edit"
        published = p.get("date_added") or None
        w(
            "INSERT INTO catalog_products "
            "(model, legacy_product_id, canonical_category_id, status, featured, content_status, sort_order, published_at) "
            f"SELECT {sqlq(model)}, {pid}, c.category_id, 1, 0, {sqlq(content_status)}, {rank}, {sqlq(published)} "
            "FROM (SELECT 1) seed LEFT JOIN catalog_categories c "
            f"ON c.legacy_category_id={sqlq(canonical_legacy_cat)} "
            "ON DUPLICATE KEY UPDATE model=VALUES(model), canonical_category_id=VALUES(canonical_category_id), "
            "status=1, content_status=VALUES(content_status), sort_order=VALUES(sort_order), published_at=VALUES(published_at);"
        )
    w("")

    w("-- Product/category relations")
    for pid in active_pids:
        for cid in sorted(set(cat_rel[pid])):
            w(
                "INSERT IGNORE INTO catalog_product_categories (product_id, category_id, sort_order) "
                "SELECT p.product_id, c.category_id, 0 FROM catalog_products p JOIN catalog_categories c "
                f"ON c.legacy_category_id={cid} WHERE p.legacy_product_id={pid};"
            )
    w("")
    w("-- Product/filter relations")
    for pid in active_pids:
        for fid in sorted(set(filter_rel[pid])):
            w(
                "INSERT IGNORE INTO catalog_product_filters (product_id, filter_id) "
                "SELECT p.product_id, f.filter_id FROM catalog_products p JOIN catalog_filters f "
                f"ON f.legacy_filter_id={fid} WHERE p.legacy_product_id={pid};"
            )
    w("")

    w("-- Product translations")
    for pid in active_pids:
        legacy_bg = desc[pid].get(2, {})
        legacy_en = desc[pid].get(1, {})
        ed = editorial.get(pid)
        for new_lang_id_var, old_lang, legacy, code in [
            ("@bg", 2, legacy_bg, "bg"), ("@en", 1, legacy_en, "en")
        ]:
            alias_slug = slug_from_alias(aliases[pid].get(old_lang))
            if code == "bg" and ed:
                name = ed.get("name") or legacy.get("name") or canonical_model(str(prod_rows[pid].get("model") or ""), legacy_bg.get("name"))
                short = ed.get("short_description")
                description = ed.get("description_html")
                meta_title = ed.get("seo_title")
                meta_desc = ed.get("meta_description")
                slug = alias_slug or re.sub(r"[^a-z0-9]+", "-", canonical_model(str(prod_rows[pid].get("model") or ""), legacy_bg.get("name")).lower()).strip("-")
            else:
                name = legacy.get("name") or canonical_model(str(prod_rows[pid].get("model") or ""), legacy_bg.get("name"))
                short = None
                description = legacy.get("description") or None
                meta_title = legacy.get("meta_title") or legacy.get("seo_title") or None
                meta_desc = strip_tags_and_entities(legacy.get("meta_description")) or None
                slug = alias_slug or re.sub(r"[^a-z0-9]+", "-", canonical_model(str(prod_rows[pid].get("model") or ""), legacy_bg.get("name")).lower()).strip("-")
            w(
                "INSERT INTO catalog_product_translations "
                "(product_id, language_id, name, slug, short_description, description, meta_title, meta_description) "
                f"SELECT p.product_id, {new_lang_id_var}, {sqlq(name)}, {sqlq(slug)}, {sqlq(short)}, {sqlq(description)}, {sqlq(meta_title)}, {sqlq(meta_desc)} "
                f"FROM catalog_products p WHERE p.legacy_product_id={pid} "
                "ON DUPLICATE KEY UPDATE name=VALUES(name), slug=VALUES(slug), short_description=VALUES(short_description), "
                "description=VALUES(description), meta_title=VALUES(meta_title), meta_description=VALUES(meta_description);"
            )
        bg_status = "reviewed" if ed else "needs_edit"
        en_status = "stale" if ed else "needs_edit"
        bg_source = (ed.get("description_html") if ed else legacy_bg.get("description")) or ""
        source_hash = hashlib.sha256(bg_source.encode("utf-8")).hexdigest() if bg_source else None
        w(
            "INSERT INTO content_translation_states "
            "(entity_type, entity_id, language_id, source_language_id, translation_status, source_hash) "
            f"SELECT 'product', p.product_id, @bg, @bg, {sqlq(bg_status)}, {sqlq(source_hash)} FROM catalog_products p WHERE p.legacy_product_id={pid} "
            "ON DUPLICATE KEY UPDATE translation_status=VALUES(translation_status), source_hash=VALUES(source_hash);"
        )
        w(
            "INSERT INTO content_translation_states "
            "(entity_type, entity_id, language_id, source_language_id, translation_status, source_hash) "
            f"SELECT 'product', p.product_id, @en, @bg, {sqlq(en_status)}, {sqlq(source_hash)} FROM catalog_products p WHERE p.legacy_product_id={pid} "
            "ON DUPLICATE KEY UPDATE translation_status=VALUES(translation_status), source_hash=VALUES(source_hash);"
        )
        w(
            "INSERT INTO content_translation_states "
            "(entity_type, entity_id, language_id, source_language_id, translation_status, source_hash) "
            f"SELECT 'product', p.product_id, @de, @bg, 'missing', {sqlq(source_hash)} FROM catalog_products p WHERE p.legacy_product_id={pid} "
            "ON DUPLICATE KEY UPDATE translation_status=VALUES(translation_status), source_hash=VALUES(source_hash);"
        )
    w("")

    w("-- Legacy product aliases for redirect/route reconstruction")
    for pid in active_pids:
        for old_lang in (2, 1):
            kw = aliases[pid].get(old_lang)
            if not kw:
                continue
            w(
                "INSERT IGNORE INTO legacy_entity_refs (entity_type, entity_id, source_system, source_type, source_id, source_path) "
                f"SELECT 'product', p.product_id, 'opencart', 'url_alias', {sqlq(f'{pid}:{old_lang}')}, {sqlq('/'+kw.lstrip('/'))} "
                f"FROM catalog_products p WHERE p.legacy_product_id={pid};"
            )
    w("")
    w("SET FOREIGN_KEY_CHECKS = 1;")

    Path(args.out).write_text("\n".join(out) + "\n", "utf-8")
    print(json.dumps({
        "active_products": len(active_pids),
        "editorial_overrides": len([pid for pid in active_pids if pid in editorial]),
        "sql_lines": len(out),
        "output": args.out,
    }, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
