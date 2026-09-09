<?php
declare(strict_types=1);

function welga_catalog_category_products_ui(int $categoryId, int $languageId, int $limit = 96): array
{
    if (!welga_db_available()) {
        return [];
    }

    $limit = max(1, min(192, $limit));
    return welga_db_all(
        "SELECT p.product_id, p.model, p.published_at, p.created_at,
                t.name, t.short_description, m.path AS image_path, sr.path AS route_path,
                GROUP_CONCAT(DISTINCT pf.filter_id ORDER BY pf.filter_id SEPARATOR ',') AS filter_ids
         FROM catalog_product_categories pc
         JOIN catalog_products p ON p.product_id = pc.product_id AND p.status = 1
         JOIN catalog_product_translations t ON t.product_id = p.product_id AND t.language_id = :language_id
         LEFT JOIN catalog_product_filters pf ON pf.product_id = p.product_id
         LEFT JOIN media m ON m.media_id = p.primary_media_id
         LEFT JOIN seo_routes sr ON sr.entity_type = 'product' AND sr.entity_id = p.product_id
             AND sr.language_id = :language_id_route AND sr.is_canonical = 1
         WHERE pc.category_id = :category_id
         GROUP BY p.product_id, p.model, p.published_at, p.created_at, t.name, t.short_description, m.path, sr.path
         ORDER BY COALESCE(p.published_at, p.created_at) DESC, p.product_id DESC
         LIMIT " . $limit,
        ['language_id' => $languageId, 'language_id_route' => $languageId, 'category_id' => $categoryId]
    );
}

function welga_catalog_attach_feature_hints(array $products, int $languageId, int $limitPerProduct = 3): array
{
    if ($products === [] || !welga_db_available()) {
        return $products;
    }

    $ids = array_values(array_unique(array_filter(array_map(
        static fn(array $product): int => (int)($product['product_id'] ?? 0),
        $products
    ))));
    if ($ids === []) {
        return $products;
    }

    $idList = implode(',', $ids);
    $rows = welga_db_all(
        "SELECT pf.product_id, pf.filter_id, ft.name
         FROM catalog_product_filters pf
         JOIN catalog_filters f ON f.filter_id = pf.filter_id AND f.status = 1
         JOIN catalog_filter_groups fg ON fg.filter_group_id = f.filter_group_id AND fg.status = 1
         JOIN catalog_filter_translations ft ON ft.filter_id = f.filter_id AND ft.language_id = :language_id
         WHERE pf.product_id IN (" . $idList . ")
         ORDER BY pf.product_id, fg.sort_order, f.sort_order, f.filter_id",
        ['language_id' => $languageId]
    );

    $featureMap = [];
    foreach ($rows as $row) {
        $productId = (int)$row['product_id'];
        if (count($featureMap[$productId] ?? []) >= $limitPerProduct) {
            continue;
        }
        $featureMap[$productId][] = [
            'filter_id' => (int)$row['filter_id'],
            'name' => (string)$row['name'],
            'icon' => welga_feature_icon_name((string)$row['name']),
        ];
    }

    foreach ($products as &$product) {
        $product['feature_hints'] = $featureMap[(int)($product['product_id'] ?? 0)] ?? [];
    }
    unset($product);

    return $products;
}

function welga_catalog_recent_gallery(int $languageId, int $limit = 8): array
{
    if (!welga_db_available()) {
        return [];
    }

    $limit = max(1, min(24, $limit));
    return welga_db_all(
        "SELECT pm.media_id, m.path, mt.alt_text, p.product_id, p.model, pt.name AS product_name,
                sr.path AS route_path
         FROM catalog_product_media pm
         JOIN media m ON m.media_id = pm.media_id AND m.status = 1
         JOIN catalog_products p ON p.product_id = pm.product_id AND p.status = 1
         JOIN catalog_product_translations pt ON pt.product_id = p.product_id AND pt.language_id = :language_id_product
         LEFT JOIN media_translations mt ON mt.media_id = m.media_id AND mt.language_id = :language_id_media
         LEFT JOIN seo_routes sr ON sr.entity_type = 'product' AND sr.entity_id = p.product_id
             AND sr.language_id = :language_id_route AND sr.is_canonical = 1
         WHERE pm.role = 'gallery'
         ORDER BY COALESCE(p.published_at, p.created_at) DESC, pm.sort_order, pm.media_id
         LIMIT " . $limit,
        [
            'language_id_product' => $languageId,
            'language_id_media' => $languageId,
            'language_id_route' => $languageId,
        ]
    );
}

function welga_catalog_category_tree(array $categories): array
{
    $byParent = [];
    foreach ($categories as $category) {
        $parent = $category['parent_id'] === null ? 0 : (int)$category['parent_id'];
        $byParent[$parent][] = $category;
    }

    $build = static function (int $parentId) use (&$build, &$byParent): array {
        $nodes = [];
        foreach ($byParent[$parentId] ?? [] as $category) {
            $category['children'] = $build((int)$category['category_id']);
            $nodes[] = $category;
        }
        return $nodes;
    };

    return $build(0);
}
