# WELGA implementation status

## Frontend vertical slice — 2026-09-08

Implemented on `frontend/design-system`:

- Real WELGA header shell with desktop navigation, product Mega Menu, search layer and BG/EN/DE language switcher.
- Mobile header pattern: WELGA + search + burger; full-screen side menu.
- Reusable product card component.
- Homepage using the frozen sequence: Hero -> asymmetric furniture composition -> New models -> Shop by Room -> upholstery story -> Gallery Module -> manufacturing/services -> EU projects -> footer.
- Product page with the frozen 01-06 structure: Model, Configurations & dimensions, Upholstery, Details, Gallery, Related models.
- Product PDF modal and image lightbox.
- Product inquiry modal UI (mail delivery backend remains to be connected before production release).
- Dynamic product options and attributes.
- Dynamic upholstery collections derived from allowed product price groups; special `F` customer-supplied upholstery rendered separately without swatches.
- Category page with responsive filters and sorting.
- Mobile sticky filter bar + bottom-sheet filter UI + dynamic result count.
- Search page by product model/name/keywords.
- Services and EU-project homepage queries.
- Responsive WELGA design system with showroom/editorial spacing, white/light neutral surfaces and restrained gold accents.
- Reveal micro-interactions with `prefers-reduced-motion` support.
- CI syntax checks for PHP 8.4 and JavaScript.

## Deliberately not considered finished yet

- Inquiry form SMTP delivery + anti-spam/rate limiting.
- Upholstery collection detail / full individual colour swatch browser.
- Dedicated Services, EU projects, Gallery, About and Contact page templates.
- Admin CRUD screens for the same modules.
- Final content migration for all 88 active products and final EN/DE editorial pass.
- Production asset copy and full database install/migration execution.
- Deployment ZIP.

This file is a status record, not a new design brief. Approved visual decisions remain frozen in `docs/design-freeze.md`.
