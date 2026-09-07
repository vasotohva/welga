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

## Upholstery price groups (current business model)

- Textile: `B`
- Eco leather: `C`, `C1`, `C2`, `C3`
- Genuine leather: `D`, `E`, `E1`, `E2`, `E3`
- Customer-supplied upholstery: `F` (special pricing mode, not a material collection)

Material types and price groups are separate configurable entities and must not be hardcoded into product logic.

## Git workflow

`main` remains deployable/stable. Development is performed in focused feature branches and merged through reviewed pull requests.

## Current status

Legacy SQL dump received and audited on 2026-09-07.

- Foundation branch: `foundation/data-model`
- Legacy audit: `docs/legacy-audit.md`
- New catalogue model: `docs/data-model.md`
- Initial MySQL/MariaDB foundation migration: `database/001_foundation.sql`
- Legacy source contains 108 products (88 active), 11 categories, 19 filters, BG/EN content and Journal-managed PDF associations.
- Next dependency: current site file archive for physical images/PDFs and detailed upholstery assets/content.
- ETKO Framework 2.0 code will be integrated once its current source archive is available to this project/repository.
