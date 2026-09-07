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

## Status

Foundation and legacy-data audit pending import of the current site archive and SQL database dump.
