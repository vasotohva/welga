<?php
declare(strict_types=1);

function welga_content_services(int $languageId, int $limit = 4): array
{
    if (!welga_db_available()) {
        return [];
    }

    $limit = max(1, min(12, $limit));
    return welga_db_all(
        'SELECT s.service_id, s.code, t.title, t.slug, t.short_description, m.path AS image_path
         FROM cms_services s
         JOIN cms_service_translations t ON t.service_id = s.service_id AND t.language_id = :language_id
         LEFT JOIN media m ON m.media_id = s.image_media_id
         WHERE s.status = 1
         ORDER BY s.sort_order, s.service_id
         LIMIT ' . $limit,
        ['language_id' => $languageId]
    );
}

function welga_content_projects(int $languageId, int $limit = 3): array
{
    if (!welga_db_available()) {
        return [];
    }

    $limit = max(1, min(12, $limit));
    return welga_db_all(
        "SELECT p.project_id, p.project_code, p.lifecycle_status, p.start_date, p.end_date,
                t.title, t.slug, t.programme, t.summary, m.path AS image_path
         FROM cms_projects p
         JOIN cms_project_translations t ON t.project_id = p.project_id AND t.language_id = :language_id
         LEFT JOIN media m ON m.media_id = p.image_media_id
         WHERE p.status = 1
         ORDER BY CASE WHEN p.lifecycle_status = 'current' THEN 0 ELSE 1 END, p.sort_order, p.project_id DESC
         LIMIT " . $limit,
        ['language_id' => $languageId]
    );
}
