<?php
declare(strict_types=1);

function welga_catalog_latest_products(int $languageId, int $limit = 8): array
{
    if (!welga_db_available()) {
        return [];
    }

    $limit = max(1, min(48, $limit));
    return welga_db_all(
        'SELECT p.product_id, p.model, p.published_at, t.name, t.slug, t.short_description, m.path AS image_path
         FROM catalog_products p
         JOIN catalog_product_translations t ON t.product_id = p.product_id AND t.language_id = :language_id
         LEFT JOIN media m ON m.media_id = p.primary_media_id
         WHERE p.status = 1
         ORDER BY COALESCE(p.published_at, p.created_at) DESC, p.product_id DESC
         LIMIT ' . $limit,
        ['language_id' => $languageId]
    );
}

function welga_catalog_categories(int $languageId): array
{
    if (!welga_db_available()) {
        return [];
    }

    return welga_db_all(
        'SELECT c.category_id, c.parent_id, c.sort_order, t.name, t.slug, m.path AS image_path
         FROM catalog_categories c
         JOIN catalog_category_translations t ON t.category_id = c.category_id AND t.language_id = :language_id
         LEFT JOIN media m ON m.media_id = c.image_media_id
         WHERE c.status = 1
         ORDER BY c.sort_order, c.category_id',
        ['language_id' => $languageId]
    );
}

function welga_catalog_product(int $productId, int $languageId): ?array
{
    if (!welga_db_available()) {
        return null;
    }

    return welga_db_one(
        'SELECT p.*, t.name, t.slug, t.short_description, t.description, t.meta_title, t.meta_description, m.path AS image_path
         FROM catalog_products p
         JOIN catalog_product_translations t ON t.product_id = p.product_id AND t.language_id = :language_id
         LEFT JOIN media m ON m.media_id = p.primary_media_id
         WHERE p.product_id = :product_id AND p.status = 1 LIMIT 1',
        ['language_id' => $languageId, 'product_id' => $productId]
    );
}

function welga_catalog_category(int $categoryId, int $languageId): ?array
{
    if (!welga_db_available()) {
        return null;
    }

    return welga_db_one(
        'SELECT c.*, t.name, t.slug, t.description, t.meta_title, t.meta_description, m.path AS image_path
         FROM catalog_categories c
         JOIN catalog_category_translations t ON t.category_id = c.category_id AND t.language_id = :language_id
         LEFT JOIN media m ON m.media_id = c.image_media_id
         WHERE c.category_id = :category_id AND c.status = 1 LIMIT 1',
        ['language_id' => $languageId, 'category_id' => $categoryId]
    );
}
