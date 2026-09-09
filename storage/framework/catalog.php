<?php
declare(strict_types=1);

function welga_catalog_latest_products(int $languageId, int $limit = 8): array
{
    if (!welga_db_available()) {
        return [];
    }

    $limit = max(1, min(48, $limit));
    return welga_db_all(
        "SELECT p.product_id, p.model, p.published_at, t.name, t.slug, t.short_description,
                m.path AS image_path, sr.path AS route_path
         FROM catalog_products p
         JOIN catalog_product_translations t ON t.product_id = p.product_id AND t.language_id = :language_id
         LEFT JOIN media m ON m.media_id = p.primary_media_id
         LEFT JOIN seo_routes sr ON sr.entity_type = 'product' AND sr.entity_id = p.product_id
             AND sr.language_id = :language_id_route AND sr.is_canonical = 1
         WHERE p.status = 1
         ORDER BY COALESCE(p.published_at, p.created_at) DESC, p.product_id DESC
         LIMIT " . $limit,
        ['language_id' => $languageId, 'language_id_route' => $languageId]
    );
}

function welga_catalog_categories(int $languageId): array
{
    if (!welga_db_available()) {
        return [];
    }

    return welga_db_all(
        "SELECT c.category_id, c.parent_id, c.sort_order, t.name, t.slug, t.description,
                m.path AS image_path, sr.path AS route_path
         FROM catalog_categories c
         JOIN catalog_category_translations t ON t.category_id = c.category_id AND t.language_id = :language_id
         LEFT JOIN media m ON m.media_id = c.image_media_id
         LEFT JOIN seo_routes sr ON sr.entity_type = 'category' AND sr.entity_id = c.category_id
             AND sr.language_id = :language_id_route AND sr.is_canonical = 1
         WHERE c.status = 1
         ORDER BY c.sort_order, c.category_id",
        ['language_id' => $languageId, 'language_id_route' => $languageId]
    );
}

function welga_catalog_product(int $productId, int $languageId): ?array
{
    if (!welga_db_available()) {
        return null;
    }

    return welga_db_one(
        "SELECT p.*, t.name, t.slug, t.short_description, t.description, t.meta_title, t.meta_description,
                m.path AS image_path, sr.path AS route_path
         FROM catalog_products p
         JOIN catalog_product_translations t ON t.product_id = p.product_id AND t.language_id = :language_id
         LEFT JOIN media m ON m.media_id = p.primary_media_id
         LEFT JOIN seo_routes sr ON sr.entity_type = 'product' AND sr.entity_id = p.product_id
             AND sr.language_id = :language_id_route AND sr.is_canonical = 1
         WHERE p.product_id = :product_id AND p.status = 1 LIMIT 1",
        ['language_id' => $languageId, 'language_id_route' => $languageId, 'product_id' => $productId]
    );
}

function welga_catalog_category(int $categoryId, int $languageId): ?array
{
    if (!welga_db_available()) {
        return null;
    }

    return welga_db_one(
        "SELECT c.*, t.name, t.slug, t.description, t.meta_title, t.meta_description,
                m.path AS image_path, sr.path AS route_path
         FROM catalog_categories c
         JOIN catalog_category_translations t ON t.category_id = c.category_id AND t.language_id = :language_id
         LEFT JOIN media m ON m.media_id = c.image_media_id
         LEFT JOIN seo_routes sr ON sr.entity_type = 'category' AND sr.entity_id = c.category_id
             AND sr.language_id = :language_id_route AND sr.is_canonical = 1
         WHERE c.category_id = :category_id AND c.status = 1 LIMIT 1",
        ['language_id' => $languageId, 'language_id_route' => $languageId, 'category_id' => $categoryId]
    );
}

function welga_catalog_category_products(int $categoryId, int $languageId, int $limit = 96): array
{
    if (!welga_db_available()) {
        return [];
    }

    $limit = max(1, min(192, $limit));
    return welga_db_all(
        "SELECT DISTINCT p.product_id, p.model, p.published_at, p.created_at,
                t.name, t.short_description, m.path AS image_path, sr.path AS route_path
         FROM catalog_product_categories pc
         JOIN catalog_products p ON p.product_id = pc.product_id AND p.status = 1
         JOIN catalog_product_translations t ON t.product_id = p.product_id AND t.language_id = :language_id
         LEFT JOIN media m ON m.media_id = p.primary_media_id
         LEFT JOIN seo_routes sr ON sr.entity_type = 'product' AND sr.entity_id = p.product_id
             AND sr.language_id = :language_id_route AND sr.is_canonical = 1
         WHERE pc.category_id = :category_id
         ORDER BY COALESCE(p.published_at, p.created_at) DESC, p.product_id DESC
         LIMIT " . $limit,
        ['language_id' => $languageId, 'language_id_route' => $languageId, 'category_id' => $categoryId]
    );
}

function welga_catalog_product_gallery(int $productId, int $languageId): array
{
    if (!welga_db_available()) {
        return [];
    }

    return welga_db_all(
        'SELECT pm.media_id, pm.role, pm.sort_order, m.path, mt.alt_text, mt.title, mt.caption
         FROM catalog_product_media pm
         JOIN media m ON m.media_id = pm.media_id AND m.status = 1
         LEFT JOIN media_translations mt ON mt.media_id = m.media_id AND mt.language_id = :language_id
         WHERE pm.product_id = :product_id
         ORDER BY pm.sort_order, pm.media_id',
        ['language_id' => $languageId, 'product_id' => $productId]
    );
}

function welga_catalog_product_documents(int $productId, int $languageId): array
{
    if (!welga_db_available()) {
        return [];
    }

    return welga_db_all(
        'SELECT d.document_id, d.document_type, d.path, d.version, pd.role, pd.sort_order,
                dt.title, dt.description
         FROM catalog_product_documents pd
         JOIN documents d ON d.document_id = pd.document_id AND d.status = 1
         LEFT JOIN document_translations dt ON dt.document_id = d.document_id AND dt.language_id = :language_id
         WHERE pd.product_id = :product_id
         ORDER BY pd.sort_order, d.document_id',
        ['language_id' => $languageId, 'product_id' => $productId]
    );
}

function welga_catalog_product_features(int $productId, int $languageId): array
{
    if (!welga_db_available()) {
        return [];
    }

    return welga_db_all(
        'SELECT fg.filter_group_id, f.filter_id, f.sort_order, fgt.name AS group_name, ft.name
         FROM catalog_product_filters pf
         JOIN catalog_filters f ON f.filter_id = pf.filter_id AND f.status = 1
         JOIN catalog_filter_groups fg ON fg.filter_group_id = f.filter_group_id AND fg.status = 1
         JOIN catalog_filter_group_translations fgt ON fgt.filter_group_id = fg.filter_group_id AND fgt.language_id = :language_id_group
         JOIN catalog_filter_translations ft ON ft.filter_id = f.filter_id AND ft.language_id = :language_id_filter
         WHERE pf.product_id = :product_id
         ORDER BY fg.sort_order, f.sort_order, f.filter_id',
        ['language_id_group' => $languageId, 'language_id_filter' => $languageId, 'product_id' => $productId]
    );
}

function welga_catalog_product_attributes(int $productId, int $languageId): array
{
    if (!welga_db_available()) {
        return [];
    }

    return welga_db_all(
        'SELECT a.attribute_id, a.code, a.data_type, a.unit, agt.name AS group_name, at.name,
                avt.name AS selected_value, pav.value_text, pav.value_number, pav.value_boolean
         FROM catalog_product_attribute_values pav
         JOIN catalog_attributes a ON a.attribute_id = pav.attribute_id AND a.status = 1
         JOIN catalog_attribute_groups ag ON ag.attribute_group_id = a.attribute_group_id AND ag.status = 1
         JOIN catalog_attribute_group_translations agt ON agt.attribute_group_id = ag.attribute_group_id AND agt.language_id = :language_id_group
         JOIN catalog_attribute_translations at ON at.attribute_id = a.attribute_id AND at.language_id = :language_id_attribute
         LEFT JOIN catalog_attribute_value_translations avt ON avt.attribute_value_id = pav.attribute_value_id AND avt.language_id = :language_id_value
         WHERE pav.product_id = :product_id AND (pav.language_id IS NULL OR pav.language_id = :language_id_row)
         ORDER BY ag.sort_order, a.sort_order, pav.sort_order, pav.product_attribute_value_id',
        [
            'language_id_group' => $languageId,
            'language_id_attribute' => $languageId,
            'language_id_value' => $languageId,
            'language_id_row' => $languageId,
            'product_id' => $productId,
        ]
    );
}

function welga_catalog_product_options(int $productId, int $languageId): array
{
    if (!welga_db_available()) {
        return [];
    }

    $rows = welga_db_all(
        'SELECT po.product_option_id, po.required, po.sort_order AS product_sort,
                o.option_id, o.code, o.input_type, ot.name AS option_name,
                ov.option_value_id, ov.code AS value_code, ov.sort_order AS value_sort,
                ovt.name AS value_name, m.path AS image_path
         FROM catalog_product_options po
         JOIN catalog_options o ON o.option_id = po.option_id AND o.status = 1
         JOIN catalog_option_translations ot ON ot.option_id = o.option_id AND ot.language_id = :language_id_option
         LEFT JOIN catalog_product_option_values pov ON pov.product_option_id = po.product_option_id AND pov.status = 1
         LEFT JOIN catalog_option_values ov ON ov.option_value_id = pov.option_value_id AND ov.status = 1
         LEFT JOIN catalog_option_value_translations ovt ON ovt.option_value_id = ov.option_value_id AND ovt.language_id = :language_id_value
         LEFT JOIN media m ON m.media_id = ov.media_id
         WHERE po.product_id = :product_id
         ORDER BY po.sort_order, po.product_option_id, ov.sort_order, ov.option_value_id',
        ['language_id_option' => $languageId, 'language_id_value' => $languageId, 'product_id' => $productId]
    );

    $groups = [];
    foreach ($rows as $row) {
        $id = (int)$row['product_option_id'];
        if (!isset($groups[$id])) {
            $groups[$id] = [
                'product_option_id' => $id,
                'option_id' => (int)$row['option_id'],
                'code' => (string)$row['code'],
                'name' => (string)$row['option_name'],
                'input_type' => (string)$row['input_type'],
                'required' => (bool)$row['required'],
                'values' => [],
            ];
        }
        if ($row['option_value_id'] !== null) {
            $groups[$id]['values'][] = [
                'option_value_id' => (int)$row['option_value_id'],
                'code' => (string)$row['value_code'],
                'name' => (string)($row['value_name'] ?? $row['value_code']),
                'image_path' => $row['image_path'],
            ];
        }
    }

    return array_values($groups);
}

function welga_catalog_product_upholstery(int $productId, int $languageId): array
{
    if (!welga_db_available()) {
        return ['groups' => [], 'collections' => []];
    }

    $groups = welga_db_all(
        'SELECT pg.price_group_id, pg.code, pg.mode, ppg.sort_order
         FROM catalog_product_price_groups ppg
         JOIN material_price_groups pg ON pg.price_group_id = ppg.price_group_id AND pg.status = 1
         WHERE ppg.product_id = :product_id
         ORDER BY ppg.sort_order, pg.sort_order, pg.price_group_id',
        ['product_id' => $productId]
    );

    $collections = welga_db_all(
        "SELECT DISTINCT c.collection_id, c.code, c.lifecycle_status, c.sort_order,
                ct.name, ct.slug, ct.short_description,
                mt.code AS material_type_code, mtt.name AS material_type_name,
                pg.code AS price_group_code, pm.path AS preview_path,
                (SELECT COUNT(*) FROM material_colors mc WHERE mc.collection_id = c.collection_id AND mc.lifecycle_status = 'active') AS color_count
         FROM catalog_product_price_groups ppg
         JOIN material_price_groups pg ON pg.price_group_id = ppg.price_group_id AND pg.status = 1 AND pg.mode = 'material'
         JOIN material_collections c ON c.price_group_id = pg.price_group_id AND c.lifecycle_status = 'active'
         JOIN material_collection_translations ct ON ct.collection_id = c.collection_id AND ct.language_id = :language_id_collection
         JOIN material_types mt ON mt.material_type_id = c.material_type_id AND mt.status = 1
         JOIN material_type_translations mtt ON mtt.material_type_id = mt.material_type_id AND mtt.language_id = :language_id_type
         LEFT JOIN media pm ON pm.media_id = c.preview_media_id
         LEFT JOIN catalog_product_material_exclusions ex ON ex.product_id = ppg.product_id AND ex.collection_id = c.collection_id AND ex.color_id IS NULL
         WHERE ppg.product_id = :product_id AND ex.exclusion_id IS NULL
         ORDER BY mt.sort_order, pg.sort_order, c.sort_order, c.collection_id",
        ['language_id_collection' => $languageId, 'language_id_type' => $languageId, 'product_id' => $productId]
    );

    return ['groups' => $groups, 'collections' => $collections];
}

function welga_catalog_related_products(int $productId, int $languageId, int $limit = 6): array
{
    if (!welga_db_available()) {
        return [];
    }

    $limit = max(1, min(12, $limit));
    return welga_db_all(
        "SELECT DISTINCT p.product_id, p.model, t.name, t.short_description, m.path AS image_path, sr.path AS route_path
         FROM catalog_product_categories source_pc
         JOIN catalog_product_categories pc ON pc.category_id = source_pc.category_id AND pc.product_id <> source_pc.product_id
         JOIN catalog_products p ON p.product_id = pc.product_id AND p.status = 1
         JOIN catalog_product_translations t ON t.product_id = p.product_id AND t.language_id = :language_id
         LEFT JOIN media m ON m.media_id = p.primary_media_id
         LEFT JOIN seo_routes sr ON sr.entity_type = 'product' AND sr.entity_id = p.product_id
             AND sr.language_id = :language_id_route AND sr.is_canonical = 1
         WHERE source_pc.product_id = :product_id
         ORDER BY COALESCE(p.published_at, p.created_at) DESC, p.product_id DESC
         LIMIT " . $limit,
        ['language_id' => $languageId, 'language_id_route' => $languageId, 'product_id' => $productId]
    );
}

function welga_catalog_category_filters(int $categoryId, int $languageId): array
{
    if (!welga_db_available()) {
        return [];
    }

    $rows = welga_db_all(
        'SELECT fg.filter_group_id, f.filter_id, fgt.name AS group_name, ft.name,
                COUNT(DISTINCT pf.product_id) AS product_count
         FROM catalog_product_categories pc
         JOIN catalog_product_filters pf ON pf.product_id = pc.product_id
         JOIN catalog_filters f ON f.filter_id = pf.filter_id AND f.status = 1
         JOIN catalog_filter_groups fg ON fg.filter_group_id = f.filter_group_id AND fg.status = 1
         JOIN catalog_filter_group_translations fgt ON fgt.filter_group_id = fg.filter_group_id AND fgt.language_id = :language_id_group
         JOIN catalog_filter_translations ft ON ft.filter_id = f.filter_id AND ft.language_id = :language_id_filter
         WHERE pc.category_id = :category_id
         GROUP BY fg.filter_group_id, f.filter_id, fgt.name, ft.name, fg.sort_order, f.sort_order
         ORDER BY fg.sort_order, fg.filter_group_id, f.sort_order, f.filter_id',
        ['language_id_group' => $languageId, 'language_id_filter' => $languageId, 'category_id' => $categoryId]
    );

    $groups = [];
    foreach ($rows as $row) {
        $groupId = (int)$row['filter_group_id'];
        if (!isset($groups[$groupId])) {
            $groups[$groupId] = ['filter_group_id' => $groupId, 'name' => (string)$row['group_name'], 'filters' => []];
        }
        $groups[$groupId]['filters'][] = [
            'filter_id' => (int)$row['filter_id'],
            'name' => (string)$row['name'],
            'product_count' => (int)$row['product_count'],
        ];
    }

    return array_values($groups);
}

function welga_catalog_search(int $languageId, string $query, int $limit = 48): array
{
    if (!welga_db_available()) {
        return [];
    }

    $query = trim($query);
    if ($query === '') {
        return [];
    }

    $limit = max(1, min(96, $limit));
    $like = '%' . $query . '%';
    return welga_db_all(
        "SELECT p.product_id, p.model, t.name, t.short_description, m.path AS image_path, sr.path AS route_path
         FROM catalog_products p
         JOIN catalog_product_translations t ON t.product_id = p.product_id AND t.language_id = :language_id
         LEFT JOIN media m ON m.media_id = p.primary_media_id
         LEFT JOIN seo_routes sr ON sr.entity_type = 'product' AND sr.entity_id = p.product_id
             AND sr.language_id = :language_id_route AND sr.is_canonical = 1
         WHERE p.status = 1 AND (p.model LIKE :model_query OR t.name LIKE :name_query OR t.search_keywords LIKE :keyword_query)
         ORDER BY CASE WHEN p.model = :exact_query THEN 0 WHEN p.model LIKE :prefix_query THEN 1 ELSE 2 END,
                  COALESCE(p.published_at, p.created_at) DESC, p.product_id DESC
         LIMIT " . $limit,
        [
            'language_id' => $languageId,
            'language_id_route' => $languageId,
            'model_query' => $like,
            'name_query' => $like,
            'keyword_query' => $like,
            'exact_query' => $query,
            'prefix_query' => $query . '%',
        ]
    );
}
