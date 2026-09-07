# WELGA migration runbook

This runbook describes the reproducible legacy OpenCart/Journal → ETKO Catalog CMS migration workflow.

## Source package

Required source files:

- legacy OpenCart SQL dump (`etkoinf_welga.sql`)
- `data.zip` / `data2.zip` legacy `image/data` assets
- `photos.zip`
- `video.zip`
- rewritten editorial JSON under `content/products/bg/`

Never import account/customer/order/password data into the catalogue migration.

## 1. Build legacy manifest

```bash
python3 tools/migration/build_legacy_manifest.py \
  --sql /source/etkoinf_welga.sql \
  --data-zip /source/data.zip \
  --data2-zip /source/data2.zip \
  --photos-zip /source/photos.zip \
  --video-zip /source/video.zip \
  --out /work/manifest
```

Expected current baseline:

- 108 products total
- 88 active products
- active model codes unique after normalization
- 228 product image references / only 3 missing, all from inactive duplicate model 250
- all active products have at least one physical technical/product document

The ZIP central directory is the filename source of truth. Do not rely on normal Linux extraction for legacy Cyrillic filenames.

## 2. Create empty ETKO database

Apply migrations in order:

```text
database/001_foundation.sql
database/002_material_price_group_independence.sql
database/003_content_workflow_and_legacy_trace.sql
database/004_wood_stain_option.sql
database/100_legacy_taxonomy_seed.sql
```

For a production installer these migrations will later be wrapped in the ETKO migration runner; during foundation work they remain explicit SQL files for auditability.

## 3. Generate active catalogue seed

```bash
python3 tools/migration/build_catalog_seed.py \
  --sql /source/etkoinf_welga.sql \
  --manifest /work/manifest/legacy-products.json \
  --manifest-helper tools/migration/build_legacy_manifest.py \
  --editorial-dir content/products/bg \
  --out /work/101_active_catalog_seed.sql
```

The generator:

- imports only the 88 active legacy products;
- normalizes model codes (for example legacy `334` → `W-334`);
- recovers anomalous codes from verified product names when needed (`Маса` → `W-354` for legacy product 154);
- imports category/filter relations;
- overlays rewritten BG content when available;
- imports legacy EN as temporary source content;
- marks EN stale and DE missing once BG has been rewritten;
- retains exact legacy aliases in `legacy_entity_refs` for route/redirect reconstruction;
- orders products newest-first by source publication date.

Run the generated SQL after the schema/taxonomy migrations.

## 4. Media and document normalization

Run the media/document builder after the product seed exists. Its responsibilities are:

1. read ZIP entries without trusting legacy extraction filenames;
2. require physical existence before import;
3. checksum every copied asset;
4. normalize production paths and filenames to ASCII-safe slugs;
5. preserve original legacy path in `media.legacy_path` / document metadata;
6. populate product primary/gallery relations;
7. extract Journal product PDF relations;
8. classify price lists separately from technical/configuration documents;
9. reject mismatched product/PDF model associations;
10. write a review report for broken/ambiguous legacy references.

Known current legacy issues that must remain rejected:

- 20 Journal PDF references have no physical source file;
- W-270 and W-349 contain a bad legacy association to `Models/271/W-271_V05-22.pdf`;
- W-358 and W-368 currently have no physical file confidently classifiable as a price list.

## 5. Upholstery library

The upholstery importer reads Journal gallery metadata together with physical `data/textiles` assets.

Rules:

- material type and price group are independent dimensions;
- preferred current mappings are recommendations only;
- folder suffixes and Journal titles are hints, never blindly trusted;
- contradictory/uncertain collections are imported as review/archive candidates, not silently published;
- colours can be archived independently of their collection;
- `F` is customer-supplied upholstery and never creates swatches.

## 6. Editorial workflow

Bulgarian is the master source.

1. Rewrite BG using `docs/product-content-standard.md`.
2. Extract reliable facts into structured attributes/options.
3. Update the BG source hash.
4. Translate final BG to EN and DE.
5. Mark translations stale automatically when BG later changes.

Legacy text is source material only; do not publish it unchanged for products that have not passed editorial review.

## 7. Routes, search and final validation

Before release:

- generate canonical language routes;
- create explicit 301 redirects where a legacy path changes;
- validate `canonical` + `hreflang` alternates;
- rebuild `search_index` for BG/EN/DE;
- validate category filter availability;
- validate product options/attributes;
- verify every public media/document relation points to an existing production file;
- verify archived upholstery is not offered as active;
- verify newest products sort first where configured.

## 8. Production deployment package

The final deployment ZIP must contain only production-required files.

Exclude:

- `.git/`
- source SQL dumps with legacy/private data
- local working manifests/reports unless explicitly needed
- secrets and production credentials
- development caches/dependencies

Provide database migrations/install step separately together with short deployment instructions.
