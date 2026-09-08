<?php
declare(strict_types=1);

require __DIR__ . '/../storage/bootstrap.php';

try {
    $route = welga_route_request();
    $language = $route['language'];
    $languageId = (int)$language['language_id'];
    $languageCode = (string)$language['code'];

    if ($route['name'] === 'redirect') {
        $target = '/' . trim((string)$route['target_path'], '/');
        welga_redirect($target === '/' ? '/' : $target . '/', (int)($route['http_code'] ?? 301));
    }

    if ($route['name'] === 'home') {
        welga_render('home', [
            'language' => $language,
            'title' => 'WELGA',
            'products' => welga_catalog_latest_products($languageId, 8),
            'categories' => welga_catalog_categories($languageId),
            'gallery' => welga_catalog_recent_gallery($languageId, 8),
            'services' => welga_content_services($languageId, 4),
            'projects' => welga_content_projects($languageId, 3),
            'bodyClass' => 'page-home',
        ]);
    }

    if ($route['name'] === 'search') {
        $query = trim((string)($_GET['q'] ?? ''));
        welga_render('search', [
            'language' => $language,
            'query' => $query,
            'products' => welga_catalog_search($languageId, $query, 48),
            'title' => welga_t('search', $languageCode) . ' | WELGA',
            'bodyClass' => 'page-search',
        ]);
    }

    if ($route['name'] === 'preview_page') {
        $pageKey = (string)($route['page_key'] ?? 'page');
        $titleKey = match ($pageKey) {
            'materials' => 'materials',
            'services' => 'services',
            'gallery' => 'gallery',
            'about' => 'about',
            'contact' => 'contact',
            'projects' => 'eu_projects',
            default => 'company',
        };
        welga_render('page/preview', [
            'language' => $language,
            'pageKey' => $pageKey,
            'title' => welga_t($titleKey, $languageCode) . ' | WELGA',
            'bodyClass' => 'page-preview page-preview-' . preg_replace('/[^a-z0-9_-]/i', '-', $pageKey),
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
                    'gallery' => welga_catalog_product_gallery($entityId, $languageId),
                    'documents' => welga_catalog_product_documents($entityId, $languageId),
                    'features' => welga_catalog_product_features($entityId, $languageId),
                    'attributes' => welga_catalog_product_attributes($entityId, $languageId),
                    'options' => welga_catalog_product_options($entityId, $languageId),
                    'upholstery' => welga_catalog_product_upholstery($entityId, $languageId),
                    'relatedProducts' => welga_catalog_related_products($entityId, $languageId, 6),
                    'title' => $product['meta_title'] ?: $product['name'],
                    'description' => (string)($product['meta_description'] ?? ''),
                    'bodyClass' => 'page-product',
                ]);
            }
        }

        if ($entityType === 'category') {
            $category = welga_catalog_category($entityId, $languageId);
            if ($category !== null) {
                welga_render('catalog/category', [
                    'language' => $language,
                    'category' => $category,
                    'products' => welga_catalog_category_products_ui($entityId, $languageId, 120),
                    'filterGroups' => welga_catalog_category_filters($entityId, $languageId),
                    'title' => $category['meta_title'] ?: $category['name'],
                    'description' => (string)($category['meta_description'] ?? ''),
                    'bodyClass' => 'page-category',
                ]);
            }
        }
    }

    welga_render('errors/404', ['language' => $language, 'title' => '404 | WELGA', 'bodyClass' => 'page-404'], 404);
} catch (Throwable $e) {
    welga_log('application', 'Unhandled request exception', ['error' => $e->getMessage()]);
    if ((bool)welga_config('app', 'debug', false)) {
        throw $e;
    }
    http_response_code(500);
    echo 'Internal Server Error';
}
