# WELGA / ETKO Catalog CMS data model

## Principles

- MySQL/MariaDB stores structured business data and relations.
- Images, PDFs and uploaded files remain on disk; the database stores metadata and paths.
- All public content is multilingual through translation tables; no `name_bg`, `name_en`, `name_de` columns.
- BG, EN and DE are initial languages; additional languages require data/configuration only.
- Products, filters, attributes, options and upholstery are separate concepts.
- Material type and upholstery price group are separate configurable entities.
- `F` is a special customer-supplied-upholstery pricing mode and never generates swatches.
- Old OpenCart IDs/URLs are retained only for migration traceability and redirects.

## Core modules

### Languages

`languages`

Central registry for language code, locale, status, default language and ordering.

### Media and documents

`media`

Metadata for images and other public assets. Physical bytes remain in the file system.

`documents`

Versionable PDFs/files such as product sheets, price lists, certificates and project documents.

`document_translations`

Translated titles/descriptions.

### Catalogue

`catalog_products`

Language-independent product data: internal ID, model, status, ordering, primary media, canonical category, legacy OpenCart ID and timestamps.

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

Options represent genuine product choices/variants, for example a model available as armchair / 2-seat / 2.5-seat / 3-seat / corner configuration when that is a real product choice.

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

Current business mapping:

- Textile → B
- Eco leather → C, C1, C2, C3
- Genuine leather → D, E, E1, E2, E3
- F → special customer-supplied-upholstery mode

The mapping is data-driven and editable in admin.

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

Public product pages derive their available upholstery dynamically from these relations, so adding/removing a collection updates every compatible product automatically.

### Optional consumption data

`catalog_product_material_consumption`

Reserved for structured upholstery consumption norms (e.g. textile metres at 1.40 m width, genuine leather m²) when reliable source data is available. PDFs remain the authoritative document during initial migration unless structured values can be verified.

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

1. Import languages and create DE.
2. Import categories + translations.
3. Import products + BG/EN legacy content into a staging/migration state.
4. Import category and filter relations.
5. Extract Journal product PDFs and attach structured documents.
6. Copy physical media/PDFs from site archive.
7. Build upholstery library from current site data/assets and client-confirmed groups.
8. Rewrite BG product copy; translate approved BG to EN/DE.
9. Generate canonical routes and legacy 301 mappings.
10. Build search index and verify filter/attribute behaviour.
