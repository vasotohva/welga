# ETKO Framework 2.0 -> WELGA integration

Source audited: `ETKOframework2-and-web(1).zip` supplied on 2026-09-08.

## Decision

WELGA uses ETKO Framework 2.0 as an architectural/security foundation, not as a copied ETKO.INFO website.

## Retained patterns

- `public_html/` separated from non-public `storage/`.
- central bootstrap and absolute application paths;
- PHP 8.4 cPanel/CloudLinux handler;
- secure session directory outside the public webroot;
- CSRF token generation/verification;
- session regeneration after admin authentication;
- admin login timeout and IP-based rate limiting;
- atomic writes for small runtime credential/state files;
- non-public private-upload directory;
- common configuration loader;
- front-controller / clean-URL architecture;
- security/cache HTTP header baseline.

## Replaced for WELGA

The following ETKO.INFO-specific systems are NOT part of WELGA:

- CSV product catalogue;
- portfolio CSV + portfolio backups;
- cart, checkout, shipping and order session logic;
- business-card and calendar product modules;
- ETKO.INFO navigation/content/pages;
- ETKO.INFO branding and project imagery;
- existing runtime sessions/logs/admin credentials.

They are replaced by the WELGA MySQL/MariaDB data model and ETKO Catalog CMS modules.

## WELGA application foundation

`application/etko-integration` introduces:

- PDO database layer using prepared statements;
- safe example + local production configuration separation;
- BG/EN/DE language resolver with DB-driven active languages;
- `seo_routes` based public router and explicit `seo_redirects` support;
- DB-backed catalogue query layer;
- protected WELGA Admin shell;
- shared view layer;
- functional home/product/category/404 views;
- runtime directories excluded from source control.

## URL strategy

- Bulgarian is the default language and does not require a language prefix.
- English uses `/en/`.
- German uses `/de/`.
- Future languages are added through `languages` + translated content, without changing the database schema.
- Entity paths are resolved from `seo_routes`; they are not hardcoded PHP filenames.

## Admin strategy

The ETKO Admin security model is retained but the portfolio editor is discarded.

WELGA Admin modules are planned as:

1. Catalogue
   - products
   - categories
   - filters
   - attributes
   - options
2. Upholstery
   - material types
   - price groups
   - suppliers
   - collections
   - colours
   - product compatibility/exceptions
3. Services
4. EU projects
5. Pages
6. Media / documents
7. Settings / languages / SEO

All content editing will operate on MySQL/MariaDB, not CSV files.

## Validation completed

- all initial WELGA application PHP files pass PHP 8.4 syntax lint;
- homepage renders with safe fallback when a database has not yet been configured;
- 404 route renders correctly;
- real production DB connection remains local/server configuration and is never committed.

## Next implementation layer

- migration/install runner for the database migrations;
- catalogue CRUD;
- upholstery CRUD and migration review queue UI;
- media/document manager;
- search/filter request layer;
- WELGA design system and final frontend modules.
