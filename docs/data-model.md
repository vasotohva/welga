# WELGA / ETKO Catalog CMS data model

## Principles

- MySQL/MariaDB stores structured business data and relations.
- Images, PDFs and uploaded files remain on disk; the database stores metadata and paths.
- All public content is multilingual through translation tables; no `name_bg`, `name_en`, `name_de` columns.
- BG, EN and DE are initial languages; additional languages require data/configuration only.
- Products, filters, attributes, options and upholstery are separate concepts.
- Material type and upholstery price group are **independent** configurable dimensions.
- Preferred material/group combinations are admin rules, not hard database constraints.
- `F` is a special customer-supplied-upholstery pricing mode and never generates swatches.
- Old OpenCart IDs/URLs are retained only for migration traceability and redirects.

## Core modules

### Languages

`languages`

Central registry for language code, locale, status, default language and ordering.

### Media and documents

`media`

Metadata for images and other public assets. Physical bytes remain in the file system. `legacy_path` preserves the exact old source path while production paths are normalized.

`documents`

Versionable PDFs/files such as product sheets, price lists, certificates and project documents.

`document_translations`

Translated titles/descriptions.

### Catalogue

`catalog_products`

Language-independent product data: internal ID, model, status, editorial state, ordering, primary media, canonical category, legacy OpenCart ID and timestamps.

`catalog_product_translations`

Name, slug, short description, full description, SEO title/description and search keywords per language.

`catalog_categories`

Tree structure with parent relation, media, status and ordering.

`catalog_category_translations`

Translated category name, slug, description and SEO fields.

`catalog_product_categories`

Many-to-many product/category relations.

`catalog_product_documents`

Product-to-document relation with role and ordering.

`catalog_product_media`

Product gallery relation.

### Filters

Filters are visitor-facing facets used to narrow a list of products.

`catalog_filter_groups`
`catalog_filter_group_translations`
`catalog_filters`
`catalog_filter_translations`
`catalog_product_filters`
`catalog_category_filters`

Category/filter relations define which filters are relevant in each catalogue section.

### Attributes

Attributes describe factual product characteristics. They are not automatically visitor-selectable options.

`catalog_attribute_groups`
`catalog_attribute_group_translations`
`catalog_attributes`
`catalog_attribute_translations`
`catalog_attribute_values`
`catalog_attribute_value_translations`
`catalog_product_attribute_values`

Attributes support controlled values and typed free values where required.

### Options

Options represent genuine product choices/variants, for example a model available with different bases, armrests, swivel mechanisms or wood stains.

`catalog_options`
`catalog_option_translations`
`catalog_option_values`
`catalog_option_value_translations`
`catalog_product_options`
`catalog_product_option_values`

No shop price/stock semantics are assumed.

## Upholstery / material library

### Material types

`material_types`
`material_type_translations`

Examples: textile, eco leather, genuine leather.

### Price groups

`material_price_groups`

Price groups do **not** own a material type. Each collection stores both its material type and its price group explicitly.

`material_type_price_group_rules`

Stores recommended/current business combinations for admin guidance:

- Textile → preferred B
- Eco leather → preferred C, C1, C2, C3
- Genuine leather → preferred D, E, E1, E2, E3
- F → special customer-supplied-upholstery mode, no swatches

Legacy WELGA data contains historical combinations outside these defaults, including textiles in C/C1/C2/D/E2. Such records remain representable and can be placed into review/archive lifecycle states rather than silently remapped.

### Suppliers, collections and colours

`material_suppliers`
`material_supplier_translations`
`material_collections`
`material_collection_translations`
`material_colors`
`material_color_translations`

Collections/colours support lifecycle states such as active, limited, temporarily unavailable, discontinuing and archived. Archived materials remain in history but are excluded from current product availability.

### Product upholstery rules

`catalog_product_price_groups`

Defines allowed price groups for a product.

`catalog_product_material_exclusions`

Allows a product to exclude a collection or colour even when its price group would normally permit it.

`catalog_product_material_overrides`

Allows explicit inclusion of an exceptional collection/colour outside the normal group rule.

Public product pages derive their available upholstery dynamically from these relations, so adding/removing a collection updates every compatible product automatically. Public UI can show counts by material type and all active swatches for the product.

### Optional consumption data

`catalog_product_material_consumption`

Reserved for structured upholstery consumption norms (e.g. textile metres at 1.40 m width, genuine leather m²) when reliable source data is available. PDFs remain the authoritative document during initial migration unless structured values can be verified.

## Editorial and translation workflow

`content_translation_states`

Tracks source language, source hash, translation status and review timestamps. Bulgarian is the initial master editorial language. EN/DE become stale when the approved BG source changes.

`legacy_entity_refs`

Generic traceability between new ETKO entities and old OpenCart/Journal IDs, aliases and source paths.

## CMS content

`cms_pages` / `cms_page_translations`

General information pages.

`cms_services` / `cms_service_translations`

Manufacturing/service pages with structured technical details and inquiry CTA support.

`cms_projects` / `cms_project_translations`

EU projects with current/completed status, programme data, dates, funding fields, documents and media.

## Search

`search_index`

A language-specific internal index for products, categories, upholstery, pages, services and projects. Initial implementation can use MySQL/MariaDB FULLTEXT plus exact/prefix matching for model codes. The abstraction keeps the door open for a dedicated search engine later without changing public URLs/content models.

## SEO and routing

- Language-specific slugs live in translation/content records.
- Canonical route is generated from the entity and selected canonical category where relevant.
- `seo_redirects` stores explicit legacy → new 301 mappings.
- Every public alternate language route outputs correct `hreflang` links.
- Missing translations use controlled fallback behaviour in admin; public publishing rules can require a translation per enabled language when desired.

## Migration sequence

1. Create foundation schema and languages.
2. Apply material/translation workflow migrations.
3. Import categories + filter vocabulary and translations.
4. Generate deterministic active catalogue seed from legacy OpenCart.
5. Overlay rewritten BG product content and mark EN/DE translation state.
6. Validate and normalize physical product media/PDFs from ZIP archives.
7. Extract Journal product PDFs into structured document relations, rejecting missing/mismatched files.
8. Build upholstery library from Journal galleries + physical swatches; uncertain combinations enter review rather than active publication.
9. Import structured options such as wood stains.
10. Generate canonical routes and legacy 301 mappings.
11. Build search index and verify filter/attribute behaviour.
12. Integrate the finished data layer into ETKO Framework 2.0 application/admin code.
