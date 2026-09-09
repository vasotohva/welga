-- WELGA / ETKO Catalog CMS
-- Product identity + technical configurations
-- Keeps model code and translated product title independent while enabling numeric sorting.

ALTER TABLE catalog_products
    ADD COLUMN model_number INT UNSIGNED DEFAULT NULL AFTER model,
    ADD KEY idx_product_model_number (model_number, model);

-- Initial migration helper for legacy codes such as 352, W-352, W_352.
-- Admin/importers should write model_number explicitly after this migration.
UPDATE catalog_products
SET model_number = CAST(NULLIF(REGEXP_REPLACE(model, '[^0-9]', ''), '') AS UNSIGNED)
WHERE model_number IS NULL;

CREATE TABLE catalog_product_configurations (
    configuration_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id INT UNSIGNED NOT NULL,
    code VARCHAR(64) NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (configuration_id),
    UNIQUE KEY uq_product_configuration_code (product_id, code),
    KEY idx_product_configuration_sort (product_id, status, sort_order),
    CONSTRAINT fk_product_configuration_product
        FOREIGN KEY (product_id) REFERENCES catalog_products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_product_configuration_translations (
    configuration_id INT UNSIGNED NOT NULL,
    language_id SMALLINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    PRIMARY KEY (configuration_id, language_id),
    CONSTRAINT fk_pct_configuration
        FOREIGN KEY (configuration_id) REFERENCES catalog_product_configurations(configuration_id) ON DELETE CASCADE,
    CONSTRAINT fk_pct_language
        FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Configuration diagrams are normal managed media, not images embedded only in PDFs.
-- Typical roles: overview, diagram, dimension, detail.
CREATE TABLE catalog_product_configuration_media (
    configuration_id INT UNSIGNED NOT NULL,
    media_id INT UNSIGNED NOT NULL,
    role VARCHAR(32) NOT NULL DEFAULT 'diagram',
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (configuration_id, media_id, role),
    KEY idx_configuration_media_sort (configuration_id, role, sort_order),
    CONSTRAINT fk_pcm_configuration
        FOREIGN KEY (configuration_id) REFERENCES catalog_product_configurations(configuration_id) ON DELETE CASCADE,
    CONSTRAINT fk_pcm_media
        FOREIGN KEY (media_id) REFERENCES media(media_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reuse the shared attribute dictionary for dimensions and technical facts.
-- Example attributes: width, depth, height, seat_height, sleeping_width.
CREATE TABLE catalog_product_configuration_attribute_values (
    configuration_attribute_value_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    configuration_id INT UNSIGNED NOT NULL,
    attribute_id INT UNSIGNED NOT NULL,
    attribute_value_id INT UNSIGNED DEFAULT NULL,
    language_id SMALLINT UNSIGNED DEFAULT NULL,
    value_text TEXT DEFAULT NULL,
    value_number DECIMAL(15,4) DEFAULT NULL,
    value_boolean TINYINT(1) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (configuration_attribute_value_id),
    KEY idx_pcav_configuration_attribute (configuration_id, attribute_id),
    CONSTRAINT fk_pcav_configuration
        FOREIGN KEY (configuration_id) REFERENCES catalog_product_configurations(configuration_id) ON DELETE CASCADE,
    CONSTRAINT fk_pcav_attribute
        FOREIGN KEY (attribute_id) REFERENCES catalog_attributes(attribute_id) ON DELETE CASCADE,
    CONSTRAINT fk_pcav_attribute_value
        FOREIGN KEY (attribute_value_id) REFERENCES catalog_attribute_values(attribute_value_id) ON DELETE SET NULL,
    CONSTRAINT fk_pcav_language
        FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
