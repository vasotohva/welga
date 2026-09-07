-- WELGA / ETKO Catalog CMS
-- Migration 006: physical file integrity metadata for documents.

SET NAMES utf8mb4;

ALTER TABLE documents
    ADD COLUMN legacy_path VARCHAR(1000) DEFAULT NULL AFTER path,
    ADD COLUMN mime_type VARCHAR(128) DEFAULT NULL AFTER legacy_url,
    ADD COLUMN file_size BIGINT UNSIGNED DEFAULT NULL AFTER mime_type,
    ADD COLUMN checksum_sha256 CHAR(64) DEFAULT NULL AFTER file_size,
    ADD KEY idx_documents_legacy_path (legacy_path(255)),
    ADD KEY idx_documents_checksum (checksum_sha256);
