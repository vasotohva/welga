#!/usr/bin/env python3
"""WELGA legacy OpenCart -> ETKO migration manifest builder.

Developer tool; reads only catalogue/content data. It deliberately ignores
customers, orders, passwords and other personal/commercial account data.

The tool reads ZIP central directories directly instead of trusting platform
extraction of old non-UTF filenames.
"""
from __future__ import annotations
import argparse, json, os, re, urllib.parse, zipfile
from collections import defaultdict
from pathlib import Path

TABLES = {
    'oc_product', 'oc_product_description', 'oc_product_image',
    'oc_product_to_category', 'oc_product_filter',
    'oc_category', 'oc_category_description',
    'oc_filter_description', 'oc_filter_group_description',
    'oc_url_alias', 'oc_journal2_modules',
}


def iter_insert_statements(sql: str, table: str):
    prefix = f"INSERT INTO `{table}`"
    pos = 0
    while True:
        start = sql.find(prefix, pos)
        if start < 0: return
        i, quoted, escaped = start, False, False
        while i < len(sql):
            ch = sql[i]
            if quoted:
                if escaped: escaped = False
                elif ch == '\\': escaped = True
                elif ch == "'": quoted = False
            else:
                if ch == "'": quoted = True
                elif ch == ';':
                    yield sql[start:i + 1]
                    pos = i + 1
                    break
            i += 1
        else: return


def split_tuples(blob: str):
    result, i, n = [], 0, len(blob)
    while i < n:
        while i < n and blob[i] in ' \r\n,\t': i += 1
        if i >= n: break
        if blob[i] != '(':
            i += 1; continue
        start, depth, quoted, escaped = i, 0, False, False
        while i < n:
            ch = blob[i]
            if quoted:
                if escaped: escaped = False
                elif ch == '\\': escaped = True
                elif ch == "'": quoted = False
            else:
                if ch == "'": quoted = True
                elif ch == '(': depth += 1
                elif ch == ')':
                    depth -= 1
                    if depth == 0:
                        result.append(blob[start + 1:i]); i += 1; break
            i += 1
    return result


def split_fields(row: str):
    result, current, quoted, escaped = [], [], False, False
    for ch in row:
        if quoted:
            current.append(ch)
            if escaped: escaped = False
            elif ch == '\\': escaped = True
            elif ch == "'": quoted = False
        else:
            if ch == "'": quoted = True; current.append(ch)
            elif ch == ',': result.append(''.join(current).strip()); current = []
            else: current.append(ch)
    result.append(''.join(current).strip())
    return result


def decode_value(raw: str):
    raw = raw.strip()
    if raw.upper() == 'NULL': return None
    if len(raw) >= 2 and raw[0] == "'" and raw[-1] == "'":
        s = raw[1:-1]
        return (s.replace("\\'", "'").replace('\\"', '"').replace('\\\\', '\\')
                 .replace('\\n', '\n').replace('\\r', '\r').replace('\\t', '\t'))
    try: return float(raw) if '.' in raw else int(raw)
    except ValueError: return raw


def load_table(sql: str, table: str):
    result = []
    for stmt in iter_insert_statements(sql, table):
        m = re.match(r"INSERT INTO `[^`]+` \((.*?)\) VALUES\s*(.*);$", stmt, re.S)
        if not m: continue
        columns = [x.strip().strip('`') for x in m.group(1).split(',')]
        for row in split_tuples(m.group(2)):
            vals = [decode_value(x) for x in split_fields(row)]
            if len(vals) == len(columns): result.append(dict(zip(columns, vals)))
    return result


def sanitize_json_controls(s: str):
    out, quoted, escaped = [], False, False
    for ch in s:
        if quoted:
            if escaped:
                out.append(ch); escaped = False; continue
            if ch == '\\': out.append(ch); escaped = True; continue
            if ch == '"': out.append(ch); quoted = False; continue
            if ord(ch) < 0x20:
                out.append({'\n':'\\n','\r':'\\r','\t':'\\t'}.get(ch, f'\\u{ord(ch):04x}'))
            else: out.append(ch)
        else:
            out.append(ch)
            if ch == '"': quoted = True
    return ''.join(out)


def collect_pdf_strings(value, path=()):
    if isinstance(value, dict):
        for k, v in value.items(): yield from collect_pdf_strings(v, path + (str(k),))
    elif isinstance(value, list):
        for i, v in enumerate(value): yield from collect_pdf_strings(v, path + (str(i),))
    elif isinstance(value, str) and '.pdf' in value.lower():
        pattern = r'(?i)(?:https?://[^"\'<>\s\\]+?\.pdf|(?:image/)?data/[^"\'<>\s\\]+?\.pdf)'
        for hit in re.findall(pattern, value.replace('\\/', '/')):
            yield path, normalize_public_path(hit)


def normalize_public_path(value: str):
    value = value.replace('\\/', '/')
    if value.startswith(('http://', 'https://')):
        value = urllib.parse.urlparse(value).path.lstrip('/')
    else:
        value = value.lstrip('/')
    if value.startswith('image/'): value = value[6:]
    return urllib.parse.unquote(value)


def archive_files(zip_path: str, public_prefix: str):
    files = set()
    with zipfile.ZipFile(zip_path) as zf:
        for info in zf.infolist():
            if info.is_dir(): continue
            files.add(public_prefix + info.filename)
    return files


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--sql', required=True)
    ap.add_argument('--data-zip', required=True)
    ap.add_argument('--data2-zip', required=True)
    ap.add_argument('--photos-zip')
    ap.add_argument('--video-zip')
    ap.add_argument('--out', required=True)
    args = ap.parse_args()

    sql = Path(args.sql).read_text('utf-8', errors='replace')
    db = {table: load_table(sql, table) for table in TABLES}

    physical = set()
    physical |= archive_files(args.data_zip, 'data/')
    physical |= archive_files(args.data2_zip, 'data/')
    if args.photos_zip: physical |= archive_files(args.photos_zip, '')
    if args.video_zip: physical |= archive_files(args.video_zip, '')

    descriptions = defaultdict(dict)
    for r in db['oc_product_description']:
        descriptions[int(r['product_id'])][int(r['language_id'])] = r
    categories, filters, galleries = defaultdict(list), defaultdict(list), defaultdict(list)
    for r in db['oc_product_to_category']: categories[int(r['product_id'])].append(int(r['category_id']))
    for r in db['oc_product_filter']: filters[int(r['product_id'])].append(int(r['filter_id']))
    for r in db['oc_product_image']: galleries[int(r['product_id'])].append(r)

    modules = {}
    for r in db['oc_journal2_modules']:
        try: modules[int(r['module_id'])] = json.loads(sanitize_json_controls(r.get('module_data') or ''))
        except json.JSONDecodeError: continue

    documents = defaultdict(list)
    material_galleries = []
    for r in db['oc_journal2_modules']:
        module_id = int(r['module_id'])
        obj = modules.get(module_id)
        if not obj: continue
        if r.get('module_type') == 'journal2_product_tabs':
            pids = []
            def collect_product_ids(x):
                if isinstance(x, dict):
                    data = x.get('data')
                    if isinstance(data, dict) and data.get('id') is not None:
                        try: pids.append(int(data.get('id')))
                        except (TypeError, ValueError): pass
                    for v in x.values(): collect_product_ids(v)
                elif isinstance(x, list):
                    for v in x: collect_product_ids(v)
            collect_product_ids(obj.get('products', []) or [])
            pids = sorted(set(pids))
            seen = set()
            for path_keys, path in collect_pdf_strings(obj):
                if path in seen: continue
                seen.add(path)
                filename = os.path.basename(path).lower()
                role = 'price_list' if ('price' in filename or 'cena' in filename) else 'technical'
                language = None
                if len(path_keys) > 1 and path_keys[0] == 'content':
                    language = {'1':'en', '2':'bg'}.get(path_keys[1])
                for pid in pids:
                    documents[pid].append({
                        'path': path, 'role': role, 'language_hint': language,
                        'exists': path in physical, 'module_id': module_id,
                    })
        elif r.get('module_type') == 'journal2_photo_gallery':
            paths = []
            def walk_images(x):
                if isinstance(x, dict):
                    for k, v in x.items():
                        if k == 'image' and isinstance(v, str) and v.startswith('data/textiles/'):
                            paths.append(v)
                        else: walk_images(v)
                elif isinstance(x, list):
                    for v in x: walk_images(v)
            walk_images(obj)
            if paths:
                material_galleries.append({
                    'module_id': module_id,
                    'legacy_name': obj.get('module_name', ''),
                    'paths': paths,
                    'existing_assets': sum(p in physical for p in paths),
                })

    products = []
    for p in sorted(db['oc_product'], key=lambda r: int(r['product_id'])):
        pid = int(p['product_id']); main_image = p.get('image') or None
        dedup_docs, seen_docs = [], set()
        for d in documents[pid]:
            key = (d['path'], d['role'], d['language_hint'])
            if key not in seen_docs: seen_docs.add(key); dedup_docs.append(d)
        products.append({
            'legacy_product_id': pid,
            'model': str(p.get('model') or '').strip(),
            'status': int(p.get('status') or 0),
            'date_added': p.get('date_added'),
            'date_modified': p.get('date_modified'),
            'main_image': main_image,
            'main_image_exists': bool(main_image and main_image in physical),
            'gallery': [
                {'path': x['image'], 'sort_order': int(x.get('sort_order') or 0), 'exists': x['image'] in physical}
                for x in sorted(galleries[pid], key=lambda x: int(x.get('sort_order') or 0))
            ],
            'category_ids': sorted(categories[pid]),
            'filter_ids': sorted(filters[pid]),
            'documents': dedup_docs,
            'legacy_names': {
                'en': descriptions[pid].get(1, {}).get('name'),
                'bg': descriptions[pid].get(2, {}).get('name'),
            },
        })

    active = [p for p in products if p['status'] == 1]
    report = {
        'products_total': len(products),
        'products_active': len(active),
        'active_models_unique': len({p['model'] for p in active}) == len(active),
        'product_images_referenced': sum((1 if p['main_image'] else 0) + len(p['gallery']) for p in products),
        'product_images_missing': sum((1 if p['main_image'] and not p['main_image_exists'] else 0) + sum(not g['exists'] for g in p['gallery']) for p in products),
        'active_products_with_physical_document': sum(any(d['exists'] for d in p['documents']) for p in active),
        'active_products_without_physical_price_list': [p['legacy_product_id'] for p in active if not any(d['exists'] and d['role'] == 'price_list' for d in p['documents'])],
        'material_gallery_modules': len(material_galleries),
        'material_gallery_assets_referenced': sum(len(g['paths']) for g in material_galleries),
    }

    out = Path(args.out); out.mkdir(parents=True, exist_ok=True)
    (out / 'legacy-products.json').write_text(json.dumps(products, ensure_ascii=False, indent=2), 'utf-8')
    (out / 'legacy-material-galleries.json').write_text(json.dumps(material_galleries, ensure_ascii=False, indent=2), 'utf-8')
    (out / 'report.json').write_text(json.dumps(report, ensure_ascii=False, indent=2), 'utf-8')
    print(json.dumps(report, ensure_ascii=False, indent=2))

if __name__ == '__main__':
    main()
