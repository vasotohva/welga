<?php
declare(strict_types=1);

require __DIR__ . '/../storage/bootstrap.php';

try {
    $route = welga_route_request();
    $language = $route['language'];
    $languageId = (int)$language['language_id'];

    if ($route['name'] === 'redirect') {
        $target = '/' . trim((string)$route['target_path'], '/');
        welga_redirect($target === '/' ? '/' : $target . '/', (int)($route['http_code'] ?? 301));
    }

    if ($route['name'] === 'home') {
        welga_render('home', [
            'language' => $language,
            'title' => 'WELGA',
            'products' => welga_catalog_latest_products($languageId, 8),
        ]);
    }

    if ($route['name'] === 'entity') {
        $entityType = (string)$route['entity_type'];
        $entityId = (int)$route['entity_id'];

        if ($entityType === 'product') {
            $product = welga_catalog_product($entityId, $languageId);
            if ($product !== null) {
                welga_render('catalog/product', [
                    'language' => $language,
                    'product' => $product,
                    'title' => $product['meta_title'] ?: $product['name'],
                    'description' => (string)($product['meta_description'] ?? ''),
                ]);
            }
        }

        if ($entityType === 'category') {
            $category = welga_catalog_category($entityId, $languageId);
            if ($category !== null) {
                welga_render('catalog/category', [
                    'language' => $language,
                    'category' => $category,
                    'title' => $category['meta_title'] ?: $category['name'],
                    'description' => (string)($category['meta_description'] ?? ''),
                ]);
            }
        }
    }

    welga_render('errors/404', ['language' => $language, 'title' => '404 | WELGA'], 404);
} catch (Throwable $e) {
    welga_log('application', 'Unhandled request exception', ['error' => $e->getMessage()]);
    if ((bool)welga_config('app', 'debug', false)) {
        throw $e;
    }
    http_response_code(500);
    echo 'Internal Server Error';
}
