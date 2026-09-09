# WELGA product admin specification

## Product identity

Product title and model identity are separate data fields in Admin but compose into one frontend product name.

Example:

- `product_title` / translated `catalog_product_translations.name`: `Тапициран стол`
- `model`: `W-352`
- `model_number`: `352`
- frontend display name: `Тапициран стол W-352`

Rules:

- `model` is language-independent and remains the canonical human-facing model code.
- `model_number` is numeric and exists for reliable sorting, admin ordering and exact/prefix search.
- Do not store the model code duplicated inside every translated title.
- Frontend display names are composed at render/search-index time.
- SEO/meta title may override the composed title when editorially required.

## Technical information

The product PDF remains the authoritative original document, but technical drawings must also exist as managed image/media assets so the product page can show them directly.

Product-level media may use roles such as:

- `technical_overview`
- `technical_diagram`
- `dimension_diagram`

For modular/configurable products use structured configurations:

- configuration code and translated name;
- one or more managed diagram images;
- structured dimensions and technical facts using the shared attribute dictionary;
- explicit order/status.

The frontend `Конфигурации и размери` section should combine:

1. large technical overview / schematic image;
2. configuration/module cards with diagrams;
3. structured dimensions/technical values;
4. separate button to open the authoritative technical PDF.

## Existing PDF migration

For legacy products:

- retain the original technical PDF;
- render/extract relevant schematic pages during migration or product cleanup;
- save the selected final diagrams as normal PNG/WebP media assets;
- link them to the product/configuration rather than generating them from the PDF on every request;
- manual review/cropping is acceptable and preferred when it produces cleaner presentation.

## New-product workflow

WELGA adds only a small number of new models per year, so Admin should optimize for completeness and quality rather than fastest possible bulk entry.

A new product can reasonably include manual work for:

- BG master title and descriptions;
- model code/number;
- categories, filters, attributes and options;
- primary/gallery photography;
- technical PDF and price-list PDF;
- technical diagrams;
- configurations and dimensions;
- upholstery price groups and exclusions;
- SEO and translations;
- final frontend preview before publishing.
