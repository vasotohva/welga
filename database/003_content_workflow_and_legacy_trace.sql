-- WELGA / ETKO Catalog CMS
-- Migration 003: editorial workflow + migration traceability.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Keep normalized production paths while retaining the exact old path for
-- redirect/migration diagnostics. New uploads leave legacy_path NULL.
ALTER TABLE media
    ADD COLUMN legacy_path VARCHAR(500) DEFAULT NULL AFTER path,
    ADD KEY idx_media_legacy_path (legacy_path(255));

-- Product publication and content quality are intentionally separate states.
-- A product can exist in the catalogue while its rewritten content is still
-- being prepared/reviewed.
ALTER TABLE catalog_products
    ADD COLUMN content_status VARCHAR(32) NOT NULL DEFAULT 'draft' AFTER featured,
    ADD KEY idx_product_content_status (content_status, status);

-- Generic source references for migrated entities which do not have a
-- dedicated legacy_* column. This table is also useful for Journal modules.
CREATE TABLE legacy_entity_refs (
    entity_type VARCHAR(64) NOT NULL,
    entity_id INT UNSIGNED NOT NULL,
    source_system VARCHAR(64) NOT NULL DEFAULT 'opencart',
    source_type VARCHAR(64) NOT NULL,
    source_id VARCHAR(128) NOT NULL,
    source_path VARCHAR(1000) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (entity_type, entity_id, source_system, source_type, source_id),
    KEY idx_legacy_source (source_system, source_type, source_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Translation lifecycle is entity-agnostic. BG is the initial master language,
-- but the model supports changing the source language later if needed.
CREATE TABLE content_translation_states (
    entity_type VARCHAR(64) NOT NULL,
    entity_id INT UNSIGNED NOT NULL,
    language_id SMALLINT UNSIGNED NOT NULL,
    source_language_id SMALLINT UNSIGNED DEFAULT NULL,
    translation_status VARCHAR(32) NOT NULL DEFAULT 'draft',
    source_hash CHAR(64) DEFAULT NULL,
    translated_source_hash CHAR(64) DEFAULT NULL,
    translated_at DATETIME DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (entity_type, entity_id, language_id),
    KEY idx_translation_status (language_id, translation_status, entity_type),
    CONSTRAINT fk_translation_state_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE,
    CONSTRAINT fk_translation_state_source_lang FOREIGN KEY (source_language_id) REFERENCES languages(language_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
