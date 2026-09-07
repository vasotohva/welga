# WELGA legacy data audit

Source: `etkoinf_welga.sql` exported 2026-09-07.

## Executive summary

The current site is an OpenCart + Journal 2 catalogue. The new WELGA site should not migrate the OpenCart application schema. We will extract only business content and relations and import them into the new ETKO Catalog CMS model.

## Database inventory

- 143 legacy tables in total.
- Languages: 2 active languages: Bulgarian (`bg`, legacy id 2) and English (`en`, legacy id 1).
- German is not present and will be added as a new language in the ETKO architecture.
- Products: 108 total; 88 active; 20 inactive.
- Product descriptions: 216 (BG + EN for all 108 products).
- Categories: 11; category descriptions: 22 (BG + EN).
- Product/category relations: 312.
- Product gallery rows: 121 across 107 products, in addition to the primary product image stored in `oc_product.image`.
- Filter groups: 2 (`Стил` / `Style`, `Предимства` / `Benefits`).
- Distinct filters: 19.
- Product/filter relations: 429.
- Attributes: legacy attribute tables exist, but there are no attribute descriptions and no product attribute rows. Attributes therefore need a clean new model rather than a direct migration.
- Options: one legacy option definition (`Този модел се изработва:`) with five values, but no `oc_product_option` or `oc_product_option_value` assignments. It is not a reliable structured source for product variants.
- Information descriptions: 36 = 18 information pages × BG/EN.
- URL aliases: 312.
- Journal 2 modules: 294.

## Product documentation

Product PDFs are not stored as structured OpenCart product documents. They are embedded inside Journal 2 product-tab module JSON/HTML.

Audit result:

- 104 Journal modules contain PDF references.
- 219 unique PDF URLs were detected.
- Product-document associations were detected for 102 products.

Example: legacy product `product_id=146`, model `334` / W-334:

- Product sheet: `/image/data/Models/334/W-334_V03-26.pdf`
- Price list: `/image/data/Models/334/W_334_price11.pdf`

Migration must extract those references into structured `documents` + `product_documents` records. The site archive is still required to copy the physical PDF files and images.

## Upholstery

The legacy database does not contain a normalized upholstery/material library. Upholstery is represented mainly through information pages and Journal content.

Relevant legacy information pages include:

- `Тапицерии`
- `Текстилни дамаски`
- `Еко кожа`
- `Естественa кожa`

The new site will therefore use a purpose-built upholstery model instead of attempting to reproduce the current static content structure.

Current agreed price-group business model:

- Textile: `B`
- Eco leather: `C`, `C1`, `C2`, `C3`
- Genuine leather: `D`, `E`, `E1`, `E2`, `E3`
- `F`: customer-supplied upholstery; special pricing mode, not a material collection.

The legacy FAQ explicitly confirms that `F` is the furniture price when upholstery is supplied by the customer. It also states that price lists contain upholstery consumption norms (textile in metres at 1.40 m width and genuine leather in square metres). We should preserve this capability structurally where source data allows it.

## Category structure

Current active tree:

- Products / Продукти
  - Soft furniture Welga / Мебели за дневна
    - Corner sofas / Ъглови дивани
    - Sofa / Канапета
    - Armchairs / Фотьойли
    - Stools / Табуретки
  - Relax Armchairs and Home Cinema / Релакс фотьойли и Домашно кино
    - Relax Armchairs / Релакс фотьойли
    - Home cinema / Домашно кино
  - Bedrooms / Спални
  - Chairs / Столове и маси

The new category model will support arbitrary depth and multilingual names/slugs rather than hardcoding this tree.

## Filters

Legacy filter groups:

### Style / Стил

- Attractive design / Атрактивен дизайн
- Classic design / Класически дизайн

### Benefits / Предимства

Examples include:

- Seat with springs / Седалка с пружини
- Chest for clothes / Ракла за дрехи
- Partially removable upholstery / Частично сваляема тапицерия
- Highly elastic foam / Високо еластична пяна
- Silicone filling / Силиконов пълнеж
- Solid wood legs / Крака от масивна дървесина
- Sleep mechanism / Механизъм за сън
- Modular system / Модулна система
- Electric/manual relax mechanism
- Double-sided pillows / Двулицеви възглавници

These values will be reviewed during migration: some are true filter facets, while others are better represented as attributes and only exposed as filters where useful.

## Product copy quality

Legacy product text must not be copied verbatim into the new site.

Example W-334 currently contains awkward catalogue phrasing, excessive line-break formatting and generated meta descriptions that duplicate content. The migration process will preserve factual claims but rewrite BG master content into consistent product copy; EN and DE will be based on the approved BG version.

No unverified construction/material claims should be invented.

## Migration principles

1. Preserve legacy IDs in dedicated `legacy_*` fields for traceability, not as new primary keys.
2. Preserve good current URLs where appropriate; otherwise create explicit 301 redirects.
3. Extract Journal PDF associations into structured document tables.
4. Copy physical images/PDFs from the site archive; the SQL dump alone does not contain their bytes.
5. Normalize filters/attributes/options rather than cloning OpenCart semantics blindly.
6. Build BG/EN/DE as first-class languages from day one, with future languages data-driven.
7. Do not migrate customer/order/cart/payment/storefront data into the new catalogue CMS unless a separate business requirement appears.
