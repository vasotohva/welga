-- WELGA / ETKO Catalog CMS
-- Foundation schema
-- Target: MySQL 8.x / MariaDB 10.5+
-- Charset: utf8mb4

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE languages (
    language_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(10) NOT NULL,
    locale VARCHAR(32) NOT NULL,
    name VARCHAR(64) NOT NULL,
    native_name VARCHAR(64) NOT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (language_id),
    UNIQUE KEY uq_languages_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE media (
    media_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(128) DEFAULT NULL,
    width INT UNSIGNED DEFAULT NULL,
    height INT UNSIGNED DEFAULT NULL,
    file_size BIGINT UNSIGNED DEFAULT NULL,
    checksum_sha256 CHAR(64) DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (media_id),
    UNIQUE KEY uq_media_path (path(255)),
    KEY idx_media_checksum (checksum_sha256)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE media_translations (
    media_id INT UNSIGNED NOT NULL,
    language_id SMALLINT UNSIGNED NOT NULL,
    alt_text VARCHAR(255) DEFAULT NULL,
    title VARCHAR(255) DEFAULT NULL,
    caption TEXT DEFAULT NULL,
    PRIMARY KEY (media_id, language_id),
    CONSTRAINT fk_media_trans_media FOREIGN KEY (media_id) REFERENCES media(media_id) ON DELETE CASCADE,
    CONSTRAINT fk_media_trans_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE documents (
    document_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    document_type VARCHAR(64) NOT NULL,
    path VARCHAR(500) NOT NULL,
    version VARCHAR(64) DEFAULT NULL,
    legacy_url VARCHAR(1000) DEFAULT NULL,
    valid_from DATE DEFAULT NULL,
    valid_to DATE DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (document_id),
    KEY idx_documents_type_status (document_type, status),
    KEY idx_documents_path (path(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE document_translations (
    document_id INT UNSIGNED NOT NULL,
    language_id SMALLINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    PRIMARY KEY (document_id, language_id),
    CONSTRAINT fk_doc_trans_doc FOREIGN KEY (document_id) REFERENCES documents(document_id) ON DELETE CASCADE,
    CONSTRAINT fk_doc_trans_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_categories (
    category_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    parent_id INT UNSIGNED DEFAULT NULL,
    image_media_id INT UNSIGNED DEFAULT NULL,
    legacy_category_id INT DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (category_id),
    UNIQUE KEY uq_category_legacy (legacy_category_id),
    KEY idx_category_parent_status (parent_id, status, sort_order),
    CONSTRAINT fk_category_parent FOREIGN KEY (parent_id) REFERENCES catalog_categories(category_id) ON DELETE SET NULL,
    CONSTRAINT fk_category_image FOREIGN KEY (image_media_id) REFERENCES media(media_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_category_translations (
    category_id INT UNSIGNED NOT NULL,
    language_id SMALLINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description MEDIUMTEXT DEFAULT NULL,
    meta_title VARCHAR(255) DEFAULT NULL,
    meta_description VARCHAR(500) DEFAULT NULL,
    PRIMARY KEY (category_id, language_id),
    KEY idx_category_trans_slug (language_id, slug),
    CONSTRAINT fk_category_trans_category FOREIGN KEY (category_id) REFERENCES catalog_categories(category_id) ON DELETE CASCADE,
    CONSTRAINT fk_category_trans_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_products (
    product_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    model VARCHAR(64) NOT NULL,
    legacy_product_id INT DEFAULT NULL,
    primary_media_id INT UNSIGNED DEFAULT NULL,
    canonical_category_id INT UNSIGNED DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    published_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id),
    UNIQUE KEY uq_product_model (model),
    UNIQUE KEY uq_product_legacy (legacy_product_id),
    KEY idx_product_status_sort (status, sort_order),
    CONSTRAINT fk_product_primary_media FOREIGN KEY (primary_media_id) REFERENCES media(media_id) ON DELETE SET NULL,
    CONSTRAINT fk_product_canonical_category FOREIGN KEY (canonical_category_id) REFERENCES catalog_categories(category_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_product_translations (
    product_id INT UNSIGNED NOT NULL,
    language_id SMALLINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    short_description TEXT DEFAULT NULL,
    description MEDIUMTEXT DEFAULT NULL,
    meta_title VARCHAR(255) DEFAULT NULL,
    meta_description VARCHAR(500) DEFAULT NULL,
    search_keywords TEXT DEFAULT NULL,
    PRIMARY KEY (product_id, language_id),
    KEY idx_product_trans_slug (language_id, slug),
    FULLTEXT KEY ft_product_trans_search (name, short_description, description, search_keywords),
    CONSTRAINT fk_product_trans_product FOREIGN KEY (product_id) REFERENCES catalog_products(product_id) ON DELETE CASCADE,
    CONSTRAINT fk_product_trans_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_product_categories (
    product_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (product_id, category_id),
    KEY idx_product_categories_category (category_id, sort_order),
    CONSTRAINT fk_pc_product FOREIGN KEY (product_id) REFERENCES catalog_products(product_id) ON DELETE CASCADE,
    CONSTRAINT fk_pc_category FOREIGN KEY (category_id) REFERENCES catalog_categories(category_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_product_media (
    product_id INT UNSIGNED NOT NULL,
    media_id INT UNSIGNED NOT NULL,
    role VARCHAR(32) NOT NULL DEFAULT 'gallery',
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (product_id, media_id, role),
    KEY idx_product_media_sort (product_id, role, sort_order),
    CONSTRAINT fk_pm_product FOREIGN KEY (product_id) REFERENCES catalog_products(product_id) ON DELETE CASCADE,
    CONSTRAINT fk_pm_media FOREIGN KEY (media_id) REFERENCES media(media_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_product_documents (
    product_id INT UNSIGNED NOT NULL,
    document_id INT UNSIGNED NOT NULL,
    role VARCHAR(64) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (product_id, document_id, role),
    KEY idx_product_documents_role (product_id, role, sort_order),
    CONSTRAINT fk_pd_product FOREIGN KEY (product_id) REFERENCES catalog_products(product_id) ON DELETE CASCADE,
    CONSTRAINT fk_pd_document FOREIGN KEY (document_id) REFERENCES documents(document_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_filter_groups (
    filter_group_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    legacy_filter_group_id INT DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (filter_group_id),
    UNIQUE KEY uq_filter_group_legacy (legacy_filter_group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_filter_group_translations (
    filter_group_id INT UNSIGNED NOT NULL,
    language_id SMALLINT UNSIGNED NOT NULL,
    name VARCHAR(128) NOT NULL,
    PRIMARY KEY (filter_group_id, language_id),
    CONSTRAINT fk_fgt_group FOREIGN KEY (filter_group_id) REFERENCES catalog_filter_groups(filter_group_id) ON DELETE CASCADE,
    CONSTRAINT fk_fgt_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_filters (
    filter_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    filter_group_id INT UNSIGNED NOT NULL,
    legacy_filter_id INT DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (filter_id),
    UNIQUE KEY uq_filter_legacy (legacy_filter_id),
    KEY idx_filter_group (filter_group_id, status, sort_order),
    CONSTRAINT fk_filter_group FOREIGN KEY (filter_group_id) REFERENCES catalog_filter_groups(filter_group_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_filter_translations (
    filter_id INT UNSIGNED NOT NULL,
    language_id SMALLINT UNSIGNED NOT NULL,
    name VARCHAR(128) NOT NULL,
    PRIMARY KEY (filter_id, language_id),
    CONSTRAINT fk_filter_trans_filter FOREIGN KEY (filter_id) REFERENCES catalog_filters(filter_id) ON DELETE CASCADE,
    CONSTRAINT fk_filter_trans_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_product_filters (
    product_id INT UNSIGNED NOT NULL,
    filter_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (product_id, filter_id),
    KEY idx_product_filters_filter (filter_id, product_id),
    CONSTRAINT fk_pf_product FOREIGN KEY (product_id) REFERENCES catalog_products(product_id) ON DELETE CASCADE,
    CONSTRAINT fk_pf_filter FOREIGN KEY (filter_id) REFERENCES catalog_filters(filter_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_category_filters (
    category_id INT UNSIGNED NOT NULL,
    filter_id INT UNSIGNED NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (category_id, filter_id),
    CONSTRAINT fk_cf_category FOREIGN KEY (category_id) REFERENCES catalog_categories(category_id) ON DELETE CASCADE,
    CONSTRAINT fk_cf_filter FOREIGN KEY (filter_id) REFERENCES catalog_filters(filter_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_attribute_groups (
    attribute_group_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (attribute_group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_attribute_group_translations (
    attribute_group_id INT UNSIGNED NOT NULL,
    language_id SMALLINT UNSIGNED NOT NULL,
    name VARCHAR(128) NOT NULL,
    PRIMARY KEY (attribute_group_id, language_id),
    CONSTRAINT fk_agt_group FOREIGN KEY (attribute_group_id) REFERENCES catalog_attribute_groups(attribute_group_id) ON DELETE CASCADE,
    CONSTRAINT fk_agt_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_attributes (
    attribute_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    attribute_group_id INT UNSIGNED NOT NULL,
    code VARCHAR(64) NOT NULL,
    data_type VARCHAR(32) NOT NULL DEFAULT 'select',
    unit VARCHAR(32) DEFAULT NULL,
    is_filterable TINYINT(1) NOT NULL DEFAULT 0,
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (attribute_id),
    UNIQUE KEY uq_attribute_code (code),
    CONSTRAINT fk_attribute_group FOREIGN KEY (attribute_group_id) REFERENCES catalog_attribute_groups(attribute_group_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_attribute_translations (
    attribute_id INT UNSIGNED NOT NULL,
    language_id SMALLINT UNSIGNED NOT NULL,
    name VARCHAR(128) NOT NULL,
    PRIMARY KEY (attribute_id, language_id),
    CONSTRAINT fk_attribute_trans_attribute FOREIGN KEY (attribute_id) REFERENCES catalog_attributes(attribute_id) ON DELETE CASCADE,
    CONSTRAINT fk_attribute_trans_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_attribute_values (
    attribute_value_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    attribute_id INT UNSIGNED NOT NULL,
    code VARCHAR(64) DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (attribute_value_id),
    KEY idx_attribute_values_attribute (attribute_id, status, sort_order),
    CONSTRAINT fk_attribute_value_attribute FOREIGN KEY (attribute_id) REFERENCES catalog_attributes(attribute_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_attribute_value_translations (
    attribute_value_id INT UNSIGNED NOT NULL,
    language_id SMALLINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    PRIMARY KEY (attribute_value_id, language_id),
    CONSTRAINT fk_avt_value FOREIGN KEY (attribute_value_id) REFERENCES catalog_attribute_values(attribute_value_id) ON DELETE CASCADE,
    CONSTRAINT fk_avt_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_product_attribute_values (
    product_attribute_value_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id INT UNSIGNED NOT NULL,
    attribute_id INT UNSIGNED NOT NULL,
    attribute_value_id INT UNSIGNED DEFAULT NULL,
    language_id SMALLINT UNSIGNED DEFAULT NULL,
    value_text TEXT DEFAULT NULL,
    value_number DECIMAL(15,4) DEFAULT NULL,
    value_boolean TINYINT(1) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (product_attribute_value_id),
    KEY idx_pav_product_attribute (product_id, attribute_id),
    CONSTRAINT fk_pav_product FOREIGN KEY (product_id) REFERENCES catalog_products(product_id) ON DELETE CASCADE,
    CONSTRAINT fk_pav_attribute FOREIGN KEY (attribute_id) REFERENCES catalog_attributes(attribute_id) ON DELETE CASCADE,
    CONSTRAINT fk_pav_value FOREIGN KEY (attribute_value_id) REFERENCES catalog_attribute_values(attribute_value_id) ON DELETE SET NULL,
    CONSTRAINT fk_pav_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_options (
    option_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(64) NOT NULL,
    input_type VARCHAR(32) NOT NULL DEFAULT 'select',
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (option_id),
    UNIQUE KEY uq_option_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_option_translations (
    option_id INT UNSIGNED NOT NULL,
    language_id SMALLINT UNSIGNED NOT NULL,
    name VARCHAR(128) NOT NULL,
    PRIMARY KEY (option_id, language_id),
    CONSTRAINT fk_option_trans_option FOREIGN KEY (option_id) REFERENCES catalog_options(option_id) ON DELETE CASCADE,
    CONSTRAINT fk_option_trans_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_option_values (
    option_value_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    option_id INT UNSIGNED NOT NULL,
    code VARCHAR(64) DEFAULT NULL,
    media_id INT UNSIGNED DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (option_value_id),
    KEY idx_option_values_option (option_id, status, sort_order),
    CONSTRAINT fk_option_value_option FOREIGN KEY (option_id) REFERENCES catalog_options(option_id) ON DELETE CASCADE,
    CONSTRAINT fk_option_value_media FOREIGN KEY (media_id) REFERENCES media(media_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_option_value_translations (
    option_value_id INT UNSIGNED NOT NULL,
    language_id SMALLINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    PRIMARY KEY (option_value_id, language_id),
    CONSTRAINT fk_option_value_trans_value FOREIGN KEY (option_value_id) REFERENCES catalog_option_values(option_value_id) ON DELETE CASCADE,
    CONSTRAINT fk_option_value_trans_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_product_options (
    product_option_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id INT UNSIGNED NOT NULL,
    option_id INT UNSIGNED NOT NULL,
    required TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (product_option_id),
    UNIQUE KEY uq_product_option (product_id, option_id),
    CONSTRAINT fk_product_option_product FOREIGN KEY (product_id) REFERENCES catalog_products(product_id) ON DELETE CASCADE,
    CONSTRAINT fk_product_option_option FOREIGN KEY (option_id) REFERENCES catalog_options(option_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_product_option_values (
    product_option_id INT UNSIGNED NOT NULL,
    option_value_id INT UNSIGNED NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (product_option_id, option_value_id),
    CONSTRAINT fk_pov_product_option FOREIGN KEY (product_option_id) REFERENCES catalog_product_options(product_option_id) ON DELETE CASCADE,
    CONSTRAINT fk_pov_value FOREIGN KEY (option_value_id) REFERENCES catalog_option_values(option_value_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE material_types (
    material_type_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(64) NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (material_type_id),
    UNIQUE KEY uq_material_type_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE material_type_translations (
    material_type_id INT UNSIGNED NOT NULL,
    language_id SMALLINT UNSIGNED NOT NULL,
    name VARCHAR(128) NOT NULL,
    description TEXT DEFAULT NULL,
    PRIMARY KEY (material_type_id, language_id),
    CONSTRAINT fk_mtt_type FOREIGN KEY (material_type_id) REFERENCES material_types(material_type_id) ON DELETE CASCADE,
    CONSTRAINT fk_mtt_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE material_price_groups (
    price_group_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(16) NOT NULL,
    material_type_id INT UNSIGNED DEFAULT NULL,
    mode VARCHAR(32) NOT NULL DEFAULT 'material',
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (price_group_id),
    UNIQUE KEY uq_price_group_code (code),
    CONSTRAINT fk_price_group_type FOREIGN KEY (material_type_id) REFERENCES material_types(material_type_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE material_suppliers (
    supplier_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(64) DEFAULT NULL,
    website VARCHAR(500) DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (supplier_id),
    KEY idx_material_supplier_status (status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE material_supplier_translations (
    supplier_id INT UNSIGNED NOT NULL,
    language_id SMALLINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    PRIMARY KEY (supplier_id, language_id),
    CONSTRAINT fk_mst_supplier FOREIGN KEY (supplier_id) REFERENCES material_suppliers(supplier_id) ON DELETE CASCADE,
    CONSTRAINT fk_mst_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE material_collections (
    collection_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    supplier_id INT UNSIGNED DEFAULT NULL,
    material_type_id INT UNSIGNED NOT NULL,
    price_group_id INT UNSIGNED NOT NULL,
    code VARCHAR(128) DEFAULT NULL,
    preview_media_id INT UNSIGNED DEFAULT NULL,
    lifecycle_status VARCHAR(32) NOT NULL DEFAULT 'active',
    valid_from DATE DEFAULT NULL,
    valid_to DATE DEFAULT NULL,
    last_verified_at DATETIME DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (collection_id),
    KEY idx_material_collection_live (material_type_id, price_group_id, lifecycle_status, sort_order),
    CONSTRAINT fk_collection_supplier FOREIGN KEY (supplier_id) REFERENCES material_suppliers(supplier_id) ON DELETE SET NULL,
    CONSTRAINT fk_collection_type FOREIGN KEY (material_type_id) REFERENCES material_types(material_type_id) ON DELETE CASCADE,
    CONSTRAINT fk_collection_group FOREIGN KEY (price_group_id) REFERENCES material_price_groups(price_group_id) ON DELETE RESTRICT,
    CONSTRAINT fk_collection_media FOREIGN KEY (preview_media_id) REFERENCES media(media_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE material_collection_translations (
    collection_id INT UNSIGNED NOT NULL,
    language_id SMALLINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    short_description TEXT DEFAULT NULL,
    description MEDIUMTEXT DEFAULT NULL,
    meta_title VARCHAR(255) DEFAULT NULL,
    meta_description VARCHAR(500) DEFAULT NULL,
    PRIMARY KEY (collection_id, language_id),
    KEY idx_material_collection_slug (language_id, slug),
    CONSTRAINT fk_mct_collection FOREIGN KEY (collection_id) REFERENCES material_collections(collection_id) ON DELETE CASCADE,
    CONSTRAINT fk_mct_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE material_colors (
    color_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    collection_id INT UNSIGNED NOT NULL,
    code VARCHAR(128) NOT NULL,
    swatch_media_id INT UNSIGNED DEFAULT NULL,
    color_hex VARCHAR(16) DEFAULT NULL,
    lifecycle_status VARCHAR(32) NOT NULL DEFAULT 'active',
    last_verified_at DATETIME DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (color_id),
    UNIQUE KEY uq_material_color_code (collection_id, code),
    KEY idx_material_color_live (collection_id, lifecycle_status, sort_order),
    CONSTRAINT fk_color_collection FOREIGN KEY (collection_id) REFERENCES material_collections(collection_id) ON DELETE CASCADE,
    CONSTRAINT fk_color_media FOREIGN KEY (swatch_media_id) REFERENCES media(media_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE material_color_translations (
    color_id INT UNSIGNED NOT NULL,
    language_id SMALLINT UNSIGNED NOT NULL,
    name VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (color_id, language_id),
    CONSTRAINT fk_mcolor_trans_color FOREIGN KEY (color_id) REFERENCES material_colors(color_id) ON DELETE CASCADE,
    CONSTRAINT fk_mcolor_trans_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE material_collection_documents (
    collection_id INT UNSIGNED NOT NULL,
    document_id INT UNSIGNED NOT NULL,
    role VARCHAR(64) NOT NULL DEFAULT 'technical_sheet',
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (collection_id, document_id, role),
    CONSTRAINT fk_mcd_collection FOREIGN KEY (collection_id) REFERENCES material_collections(collection_id) ON DELETE CASCADE,
    CONSTRAINT fk_mcd_document FOREIGN KEY (document_id) REFERENCES documents(document_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_product_price_groups (
    product_id INT UNSIGNED NOT NULL,
    price_group_id INT UNSIGNED NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (product_id, price_group_id),
    CONSTRAINT fk_ppg_product FOREIGN KEY (product_id) REFERENCES catalog_products(product_id) ON DELETE CASCADE,
    CONSTRAINT fk_ppg_group FOREIGN KEY (price_group_id) REFERENCES material_price_groups(price_group_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_product_material_exclusions (
    exclusion_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id INT UNSIGNED NOT NULL,
    collection_id INT UNSIGNED DEFAULT NULL,
    color_id INT UNSIGNED DEFAULT NULL,
    reason VARCHAR(500) DEFAULT NULL,
    PRIMARY KEY (exclusion_id),
    KEY idx_material_exclusion_product (product_id),
    CONSTRAINT fk_material_exclusion_product FOREIGN KEY (product_id) REFERENCES catalog_products(product_id) ON DELETE CASCADE,
    CONSTRAINT fk_material_exclusion_collection FOREIGN KEY (collection_id) REFERENCES material_collections(collection_id) ON DELETE CASCADE,
    CONSTRAINT fk_material_exclusion_color FOREIGN KEY (color_id) REFERENCES material_colors(color_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_product_material_overrides (
    override_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id INT UNSIGNED NOT NULL,
    collection_id INT UNSIGNED DEFAULT NULL,
    color_id INT UNSIGNED DEFAULT NULL,
    note VARCHAR(500) DEFAULT NULL,
    PRIMARY KEY (override_id),
    KEY idx_material_override_product (product_id),
    CONSTRAINT fk_material_override_product FOREIGN KEY (product_id) REFERENCES catalog_products(product_id) ON DELETE CASCADE,
    CONSTRAINT fk_material_override_collection FOREIGN KEY (collection_id) REFERENCES material_collections(collection_id) ON DELETE CASCADE,
    CONSTRAINT fk_material_override_color FOREIGN KEY (color_id) REFERENCES material_colors(color_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_product_material_consumption (
    consumption_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id INT UNSIGNED NOT NULL,
    material_type_id INT UNSIGNED NOT NULL,
    option_value_id INT UNSIGNED DEFAULT NULL,
    quantity DECIMAL(12,3) NOT NULL,
    unit VARCHAR(16) NOT NULL,
    reference_width_cm DECIMAL(8,2) DEFAULT NULL,
    note VARCHAR(500) DEFAULT NULL,
    PRIMARY KEY (consumption_id),
    KEY idx_consumption_product (product_id, material_type_id),
    CONSTRAINT fk_consumption_product FOREIGN KEY (product_id) REFERENCES catalog_products(product_id) ON DELETE CASCADE,
    CONSTRAINT fk_consumption_type FOREIGN KEY (material_type_id) REFERENCES material_types(material_type_id) ON DELETE CASCADE,
    CONSTRAINT fk_consumption_option_value FOREIGN KEY (option_value_id) REFERENCES catalog_option_values(option_value_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cms_pages (
    page_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(128) NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (page_id),
    UNIQUE KEY uq_cms_page_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cms_page_translations (
    page_id INT UNSIGNED NOT NULL,
    language_id SMALLINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    content MEDIUMTEXT DEFAULT NULL,
    meta_title VARCHAR(255) DEFAULT NULL,
    meta_description VARCHAR(500) DEFAULT NULL,
    PRIMARY KEY (page_id, language_id),
    CONSTRAINT fk_page_trans_page FOREIGN KEY (page_id) REFERENCES cms_pages(page_id) ON DELETE CASCADE,
    CONSTRAINT fk_page_trans_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cms_services (
    service_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(128) NOT NULL,
    image_media_id INT UNSIGNED DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (service_id),
    UNIQUE KEY uq_service_code (code),
    CONSTRAINT fk_service_media FOREIGN KEY (image_media_id) REFERENCES media(media_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cms_service_translations (
    service_id INT UNSIGNED NOT NULL,
    language_id SMALLINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    short_description TEXT DEFAULT NULL,
    content MEDIUMTEXT DEFAULT NULL,
    technical_details MEDIUMTEXT DEFAULT NULL,
    meta_title VARCHAR(255) DEFAULT NULL,
    meta_description VARCHAR(500) DEFAULT NULL,
    PRIMARY KEY (service_id, language_id),
    CONSTRAINT fk_service_trans_service FOREIGN KEY (service_id) REFERENCES cms_services(service_id) ON DELETE CASCADE,
    CONSTRAINT fk_service_trans_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cms_projects (
    project_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_code VARCHAR(128) DEFAULT NULL,
    lifecycle_status VARCHAR(32) NOT NULL DEFAULT 'current',
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    funding_amount DECIMAL(15,2) DEFAULT NULL,
    currency_code CHAR(3) DEFAULT 'EUR',
    image_media_id INT UNSIGNED DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (project_id),
    KEY idx_project_lifecycle (lifecycle_status, status, sort_order),
    CONSTRAINT fk_project_media FOREIGN KEY (image_media_id) REFERENCES media(media_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cms_project_translations (
    project_id INT UNSIGNED NOT NULL,
    language_id SMALLINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    programme VARCHAR(500) DEFAULT NULL,
    summary TEXT DEFAULT NULL,
    content MEDIUMTEXT DEFAULT NULL,
    objectives MEDIUMTEXT DEFAULT NULL,
    activities MEDIUMTEXT DEFAULT NULL,
    results MEDIUMTEXT DEFAULT NULL,
    meta_title VARCHAR(255) DEFAULT NULL,
    meta_description VARCHAR(500) DEFAULT NULL,
    PRIMARY KEY (project_id, language_id),
    CONSTRAINT fk_project_trans_project FOREIGN KEY (project_id) REFERENCES cms_projects(project_id) ON DELETE CASCADE,
    CONSTRAINT fk_project_trans_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cms_project_documents (
    project_id INT UNSIGNED NOT NULL,
    document_id INT UNSIGNED NOT NULL,
    role VARCHAR(64) NOT NULL DEFAULT 'document',
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (project_id, document_id, role),
    CONSTRAINT fk_project_doc_project FOREIGN KEY (project_id) REFERENCES cms_projects(project_id) ON DELETE CASCADE,
    CONSTRAINT fk_project_doc_document FOREIGN KEY (document_id) REFERENCES documents(document_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE seo_routes (
    route_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    language_id SMALLINT UNSIGNED NOT NULL,
    entity_type VARCHAR(64) NOT NULL,
    entity_id INT UNSIGNED NOT NULL,
    path VARCHAR(500) NOT NULL,
    is_canonical TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (route_id),
    UNIQUE KEY uq_seo_route_path (language_id, path(255)),
    KEY idx_seo_route_entity (entity_type, entity_id, language_id),
    CONSTRAINT fk_seo_route_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE seo_redirects (
    redirect_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    language_id SMALLINT UNSIGNED DEFAULT NULL,
    source_path VARCHAR(500) NOT NULL,
    target_path VARCHAR(500) NOT NULL,
    http_code SMALLINT UNSIGNED NOT NULL DEFAULT 301,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (redirect_id),
    UNIQUE KEY uq_redirect_source (source_path(255)),
    KEY idx_redirect_status (status),
    CONSTRAINT fk_redirect_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE search_index (
    search_index_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    language_id SMALLINT UNSIGNED NOT NULL,
    entity_type VARCHAR(64) NOT NULL,
    entity_id INT UNSIGNED NOT NULL,
    route_path VARCHAR(500) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content MEDIUMTEXT DEFAULT NULL,
    keywords TEXT DEFAULT NULL,
    model_code VARCHAR(128) DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (search_index_id),
    UNIQUE KEY uq_search_entity (language_id, entity_type, entity_id),
    KEY idx_search_model (model_code),
    FULLTEXT KEY ft_search_content (title, content, keywords),
    CONSTRAINT fk_search_lang FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial languages. New languages are data, not schema changes.
INSERT INTO languages (language_id, code, locale, name, native_name, is_default, status, sort_order) VALUES
(1, 'bg', 'bg-BG', 'Bulgarian', 'Български', 1, 1, 10),
(2, 'en', 'en-GB', 'English', 'English', 0, 1, 20),
(3, 'de', 'de-DE', 'German', 'Deutsch', 0, 1, 30);

-- Configurable upholstery material types.
INSERT INTO material_types (material_type_id, code, status, sort_order) VALUES
(1, 'textile', 1, 10),
(2, 'eco_leather', 1, 20),
(3, 'genuine_leather', 1, 30);

INSERT INTO material_type_translations (material_type_id, language_id, name) VALUES
(1, 1, 'Текстил'), (1, 2, 'Textile'), (1, 3, 'Textil'),
(2, 1, 'Еко кожа'), (2, 2, 'Eco leather'), (2, 3, 'Kunstleder'),
(3, 1, 'Естествена кожа'), (3, 2, 'Genuine leather'), (3, 3, 'Echtleder');

-- Current WELGA price-group business model. This remains editable data.
INSERT INTO material_price_groups (price_group_id, code, material_type_id, mode, status, sort_order) VALUES
(1,  'B',  1, 'material', 1, 10),
(2,  'C',  2, 'material', 1, 20),
(3,  'C1', 2, 'material', 1, 21),
(4,  'C2', 2, 'material', 1, 22),
(5,  'C3', 2, 'material', 1, 23),
(6,  'D',  3, 'material', 1, 30),
(7,  'E',  3, 'material', 1, 40),
(8,  'E1', 3, 'material', 1, 41),
(9,  'E2', 3, 'material', 1, 42),
(10, 'E3', 3, 'material', 1, 43),
(11, 'F', NULL, 'customer_supplied', 1, 50);

SET FOREIGN_KEY_CHECKS = 1;
