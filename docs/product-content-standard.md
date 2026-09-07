# Product content standard

The legacy OpenCart descriptions are source material, not publish-ready copy.

## Editorial hierarchy

Each product is prepared in Bulgarian first. Bulgarian is the master editorial version.

1. **Product name / H1** — concise model + product type; avoid repeating `Велга` in every title when brand context is already obvious.
2. **Short description** — 1-2 useful sentences describing the model and its main functional/design distinction.
3. **Main description** — natural Bulgarian prose; factual, specific and easy to scan.
4. **Structured attributes** — construction, mechanisms, materials, dimensions, sleep function, storage, base/legs etc. belong in attributes whenever they can be represented reliably.
5. **Options** — only real customer choices, e.g. base variant, armrests, swivel mechanism, wood stain.
6. **Upholstery** — generated dynamically from material type + allowed price groups + exclusions/overrides. Do not hard-code dozens of colours into product copy.
7. **Documents** — technical/configuration PDF and price-list PDF are separate structured relations.

## Naming convention

Preferred public pattern for numbered furniture models:

`W-### – <product type>`

Examples:

- `W-368 – разтегателно 3-местно канапе`
- `W-358 – тапициран бар стол`
- `W-366 – тапициран стол`

Legacy names such as `Тапицирано канапе мека мебел Велга - 368` are retained only as migration/source data.

## Language quality

Avoid:

- keyword stuffing;
- generic claims such as `перфектен`, `безкомпромисен`, `уникален`, `инвестиция в комфорт` unless there is a concrete reason;
- unnecessary repetitions of model/brand/category names;
- claims which are not present in the source data;
- invented materials, densities, mechanisms, certifications or durability figures.

Prefer:

- precise product terminology;
- concise sentences;
- consistent units: `мм`, `см`, `л`, `°` with proper spacing;
- explicit option wording (`предлага се`, `може да бъде`, `опция`) instead of implying standard equipment;
- factual explanations of what a feature changes for the user.

## Source-of-truth rule

A rewrite may improve language, structure and clarity but **must not introduce a technical fact absent from the legacy product description, PDF, structured filter/attribute data or another verified WELGA source**.

When sources disagree, the value is not guessed. It is placed in the migration/content review queue.

## Structured extraction

Where a legacy paragraph contains a stable fact, it should be moved/duplicated into a structured attribute or option, for example:

- sleep area;
- storage volume;
- manual/electric relax mechanism;
- swivel / auto-return mechanism;
- base type;
- RAL finish option;
- solid-wood legs;
- removable upholstery;
- sleep function;
- available configuration/base variants.

The narrative description should not become a raw database dump of these facts.

## Translation workflow

1. Rewrite and approve BG.
2. Compute/store the BG source hash.
3. Translate EN and DE from the final BG version, while consulting technical source terms where necessary.
4. Store the translated source hash.
5. If BG later changes, EN/DE are marked `stale` until updated.

Future languages use the same workflow without schema changes.

## SEO

SEO fields are generated from the final edited content, not copied from legacy keyword-stuffed fields.

- one clear H1;
- concise unique meta title;
- natural meta description;
- stable language-specific slug;
- descriptive image alt text;
- Product/Breadcrumb structured data where applicable;
- canonical + hreflang managed by the routing layer.
