# Legacy file / asset audit

Source package audited on 2026-09-07:

- `etkoinf_welga.sql`
- `data(1).zip`
- `data2(1).zip`
- `photos(1).zip`
- `video(1).zip`

## Package size and coverage

The four file archives contain **2,448 files / ~571 MB uncompressed**.

Important asset areas:

- `Models/` — product photography and product PDFs
- `textiles/` — upholstery swatches and legacy collections
- `pages/` — page/editorial imagery
- `EU_FUNDS/` — EU-project assets
- `banners/`, `menu/`, `journal2/` — legacy presentation assets
- `photos/` — additional corporate/editorial assets
- `video/` — two MP4 files, including the 30th-anniversary video

Main file types in `data + data2`:

- JPG/JPEG: >2,100
- PDF: 203 physical PDFs in `data`, predominantly model documents
- PNG: >100
- GIF: legacy decorative assets

## Product media completeness

The OpenCart database references 228 product images when the primary images and `oc_product_image` gallery rows are combined.

**225 / 228 referenced files are physically present.**

The only three missing product images belong to legacy **inactive product_id 75 / model 250**, which is also a duplicate model record. This should not block migration of the active catalogue.

Migration rule:

1. Import physical assets only after existence validation.
2. Never create a new media record for a missing legacy file.
3. Flag missing assets in the migration report.
4. Preserve inactive legacy products as migration history where useful, but do not publish them automatically.

## Product PDF audit

Journal product-tab modules contain product PDF links rather than normal OpenCart product/document relations.

Findings:

- PDF tabs found for **102 products**.
- The six products without PDF-tab modules are all inactive legacy records.
- 217 distinct PDF paths are referenced in Journal module content.
- 198 physical PDFs are present under `Models/`.
- All but one physical model PDF are referenced by Journal; the unlinked physical file is `data/Models/240/W-240.pdf`.
- 20 Journal PDF references point to files not present in the supplied archive. These must be treated as broken legacy references, not migrated as active documents.

Document classification for the new CMS:

- filenames containing `price` / legacy price naming -> `price_list`
- product/configuration/specification PDFs -> `technical`
- ambiguous files -> migration review queue

The importer must preserve `legacy_url` and physical filename while storing the new normalized document relation in `catalog_product_documents`.

## Upholstery asset library

`data2/textiles/` contains **963 physical files**.

Journal photo-gallery modules expose approximately **40 upholstery collections** and reference ~930 physical swatches. Another 34 swatch files exist on disk but are not referenced by the active Journal gallery configuration; these are migration candidates for `archived` / `review`, not automatic publication.

Examples recovered from the current legacy CMS configuration:

### Eco leather

- AMARILLO — C1
- Bull — C2
- Crush — C
- Shadow — C
- Skin — C

### Genuine leather

- CLASSIC SADDLE — legacy gallery has no explicit price group
- HERMES — D
- KENIA — E1
- SUPREME BUTTERSOFT — E2
- TOLEDO — E
- TRENTO — E3
- VINTAGE — E1

### Textile

The legacy system contains textile collections across several price groups, not only B. Examples:

- ASTON — B
- Chester — B
- Hugo — B
- Linea — B
- Solid — B
- Trinity — B
- Twist — B
- Velluto — C
- Baltimore — C
- Board — C
- Chill me — C
- Luna — C
- Seven — C
- Adore — C1
- Brake — C1
- Cool — C1
- Hold me — C1
- Jazz — C1
- Kiss — C1
- Fjord — C2
- Ground — C2
- Piquet — C2
- Shadow — C2
- Solution — C2
- Modena — D
- Honey — E2

There are also unreferenced/legacy swatches such as the physical Milano B set.

## Critical legacy inconsistencies

The archive proves that material type and price group cannot be inferred safely from a folder name alone.

Examples:

- Journal identifies **HERMES as genuine leather D**, while the physical folder is named `HERMES_E`.
- AMARILLO is physically stored under `Eco-leather`, but one Journal module title calls it `Текстил AMARILLO C1`.
- multiple textile collections are historically assigned C/C1/C2/D/E2 although the current preferred business mapping is Textile -> B.

Therefore:

- **material type** and **price group** are independent database dimensions;
- folder suffixes are legacy hints only;
- Journal gallery metadata is also not blindly trusted;
- current business defaults are stored as configurable admin rules;
- legacy combinations outside the current defaults are imported as `review` / `archived` unless confirmed active;
- nothing is silently deleted or remapped.

## Current preferred business defaults

These defaults are used by the administration as recommendations, not hard database constraints:

- Textile -> B
- Eco leather -> C, C1, C2, C3
- Genuine leather -> D, E, E1, E2, E3
- F -> customer-supplied upholstery; special pricing mode, no swatches

This policy keeps the new CMS compatible with both current WELGA rules and historical data.

## Asset migration policy

The production application will not retain the old `image/data/...` structure as its internal model.

Migration will:

1. checksum assets;
2. normalize paths into the new media structure;
3. keep the original legacy path for traceability;
4. create product/gallery/document relations from structured data;
5. preserve old public URLs through redirects where required;
6. keep discontinued upholstery collections and colors in history while excluding them from current public selection.
