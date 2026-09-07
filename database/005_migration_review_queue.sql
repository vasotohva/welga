-- WELGA / ETKO Catalog CMS
-- Migration 005: explicit review queue for ambiguous/broken legacy data.
--
-- Migration must never silently guess when legacy sources conflict. Items remain
-- traceable here until reviewed or intentionally ignored.

SET NAMES utf8mb4;

CREATE TABLE migration_review_queue (
    review_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    source_system VARCHAR(64) NOT NULL DEFAULT 'opencart',
    entity_type VARCHAR(64) NOT NULL,
    legacy_entity_id VARCHAR(128) DEFAULT NULL,
    issue_code VARCHAR(64) NOT NULL,
    severity VARCHAR(16) NOT NULL DEFAULT 'warning',
    status VARCHAR(24) NOT NULL DEFAULT 'open',
    source_path VARCHAR(1000) DEFAULT NULL,
    message TEXT NOT NULL,
    payload_json JSON DEFAULT NULL,
    resolved_note TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME DEFAULT NULL,
    PRIMARY KEY (review_id),
    KEY idx_review_open (status, severity, entity_type),
    KEY idx_review_legacy (entity_type, legacy_entity_id),
    KEY idx_review_issue (issue_code, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
