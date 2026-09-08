# WELGA UI / design freeze

This file captures decisions already agreed in the dedicated WELGA design/UI conversation. These are implementation constraints, not open design questions.

## Positioning

- WELGA is a manufacturer/showroom/B2B catalogue, not an online shop.
- No cart, checkout, sale badges, promotional price language or generic ecommerce trust blocks.
- Product pages may expose PDF documents and inquiry actions, but not normal ecommerce purchase UI.
- Bulgarian is the default language. English is secondary. The architecture remains extensible for German and future languages.

## Visual direction

- White as the primary surface.
- Very light neutral gray secondary surfaces.
- Muted / refined gold accents.
- Do not use the green ecommerce look from Journal demos.
- Spacious editorial showroom language rather than dense catalogue/shop UI.
- Strong use of icons, motion and micro-interactions where they improve orientation and perceived quality.
- Newest products must appear first / most visible in relevant product lists and highlights.

## Already defined

- Homepage direction and hierarchy.
- Desktop header and mega-menu direction.
- Product page structure.
- Product gallery behavior.
- Dimensions / configurations section.
- Upholstery / swatch presentation.
- PDF modal behavior.
- Product inquiry modal.
- Shop by Room as an important homepage/content module.
- Gallery Module as part of the design system.

## Mobile decisions already fixed

- Compact header pattern: WELGA logo + search + burger.
- Category filtering uses a sticky filter bar.
- Filters open in a drawer / bottom-sheet pattern.
- Filter groups use accordion behavior.
- Primary filter action uses a result-count CTA such as `Покажи N модела`.

## Remaining design surfaces

These still need implementation/design refinement, but must be built consistently with the frozen system above:

- Category page + filters.
- Gallery and secondary content pages.
- Footer.
- Detailed micro-interaction pass.

## Implementation rule

Do not restart visual exploration for already approved surfaces unless a concrete technical or usability issue is found during implementation. Build from the agreed UI direction and adapt only where real WELGA data/assets expose a specific problem.
