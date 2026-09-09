<?php
declare(strict_types=1);

function welga_route_request(): array
{
    $context = welga_language_context(welga_request_path());
    $language = $context['language'];
    $path = trim((string)$context['path'], '/');

    if ($path === '') {
        return ['name' => 'home', 'entity_type' => 'home', 'entity_id' => null] + $context;
    }

    if ($path === 'search') {
        return ['name' => 'search', 'entity_type' => 'search', 'entity_id' => null] + $context;
    }

    // Development-friendly secondary routes. These keep the shared navigation usable
    // while their final CMS-backed page modules are still being implemented.
    $previewPages = [
        'tapicerii' => 'materials',
        'welga-uslugi' => 'services',
        'galeria' => 'gallery',
        'za-nas' => 'about',
        'kontakti' => 'contact',
        'tekushti-evroproekti' => 'projects',
    ];
    if (isset($previewPages[$path])) {
        return ['name' => 'preview_page', 'page_key' => $previewPages[$path], 'entity_type' => 'page', 'entity_id' => null] + $context;
    }

    if (!welga_db_available()) {
        return ['name' => 'not_found', 'entity_type' => null, 'entity_id' => null] + $context;
    }

    $route = welga_db_one(
        'SELECT entity_type, entity_id FROM seo_routes WHERE language_id = :language_id AND path = :path AND is_canonical = 1 LIMIT 1',
        ['language_id' => (int)$language['language_id'], 'path' => $path]
    );

    if ($route !== null) {
        return ['name' => 'entity'] + $route + $context;
    }

    $redirect = welga_db_one(
        'SELECT target_path, http_code FROM seo_redirects WHERE status = 1 AND source_path = :path LIMIT 1',
        ['path' => $path]
    );
    if ($redirect !== null) {
        return ['name' => 'redirect'] + $redirect + $context;
    }

    return ['name' => 'not_found', 'entity_type' => null, 'entity_id' => null] + $context;
}
