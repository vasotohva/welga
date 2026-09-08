# WELGA

New multilingual corporate and furniture catalogue website for [welga.com](https://welga.com/), built on ETKO Framework 2.0.

## Core direction

- PHP + MySQL/MariaDB
- `public_html/` + non-public `storage/` architecture
- Custom ETKO Catalog CMS, without OpenCart storefront logic
- Multilingual from day one: BG, EN, DE; extensible to more languages
- Structured products, categories, filters, attributes and options
- Upholstery/material library with configurable material types and price groups
- Product PDF price lists and technical documents
- Services and EU projects as structured CMS modules
- Search, SEO, canonical/hreflang and redirects designed into the foundation
- Migration from the current WELGA/OpenCart data source

## Upholstery price groups

Material type and price group are independent configurable dimensions. Current WELGA business rules are stored as recommended admin mappings, not hard database constraints:

- Textile: preferred `B`
- Eco leather: preferred `C`, `C1`, `C2`, `C3`
- Genuine leather: preferred `D`, `E`, `E1`, `E2`, `E3`
- Customer-supplied upholstery: `F` (special pricing mode, not a material collection)

Legacy data contains historical exceptions, so imports outside the preferred mapping are preserved for review instead of being silently changed or deleted.

## Git workflow

`main` remains deployable/stable. Development is performed in focused feature branches and merged through reviewed pull requests.

## Current status

Legacy SQL and complete server asset package were received and audited on 2026-09-07.

- Foundation branch: `foundation/data-model`
- Draft PR: `#1`
- Legacy audit: `docs/legacy-audit.md`
- Asset audit: `docs/asset-audit.md`
- New catalogue model: `docs/data-model.md`
- Product editorial standard: `docs/product-content-standard.md`
- Initial MySQL/MariaDB foundation: `database/001_foundation.sql` + follow-up migrations
- Legacy source: 108 products / 88 active, 11 categories, 19 filters, BG/EN content, Journal-managed PDFs and upholstery galleries
- File package: 2,448 files / ~571 MB, including product photography, PDFs, upholstery swatches, corporate photos and video
- Active catalogue models are unique and can be migrated cleanly; inactive duplicates remain legacy history only
- Product BG copy is being rewritten newest-first; first two editorial batches are committed
- Deterministic migration tools exist for legacy manifest generation and active catalogue seed generation
- Media/document normalization and upholstery import are the next migration layers

The remaining application-level dependency is the current ETKO Framework 2.0 source archive. Until it is available, data migration, content, database schema and asset preparation continue independently.
