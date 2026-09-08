-- WELGA / ETKO Catalog CMS
-- Migration 002: material type and price group must be independent dimensions.
--
-- Legacy WELGA data proves that the same price-group family has historically
-- been used across more than one material type. Therefore a price group must
-- not own a material_type_id. Collections carry BOTH dimensions explicitly.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE material_price_groups
    DROP FOREIGN KEY fk_price_group_type;

ALTER TABLE material_price_groups
    DROP COLUMN material_type_id;

CREATE TABLE material_type_price_group_rules (
    material_type_id INT UNSIGNED NOT NULL,
    price_group_id INT UNSIGNED NOT NULL,
    rule_type VARCHAR(32) NOT NULL DEFAULT 'recommended',
    status TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (material_type_id, price_group_id),
    CONSTRAINT fk_mtpg_rule_type FOREIGN KEY (material_type_id) REFERENCES material_types(material_type_id) ON DELETE CASCADE,
    CONSTRAINT fk_mtpg_rule_group FOREIGN KEY (price_group_id) REFERENCES material_price_groups(price_group_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Current business defaults. These are ADMIN/UI rules, not hard DB constraints.
-- Historical collections outside these combinations remain importable and can
-- be marked for review instead of being silently remapped or deleted.
INSERT INTO material_type_price_group_rules (material_type_id, price_group_id, rule_type, status, sort_order)
SELECT mt.material_type_id, pg.price_group_id, 'recommended', 1,
       CASE pg.code
           WHEN 'B' THEN 10 WHEN 'C' THEN 20 WHEN 'C1' THEN 21 WHEN 'C2' THEN 22 WHEN 'C3' THEN 23
           WHEN 'D' THEN 30 WHEN 'E' THEN 40 WHEN 'E1' THEN 41 WHEN 'E2' THEN 42 WHEN 'E3' THEN 43
           ELSE 100
       END
FROM material_types mt
JOIN material_price_groups pg
WHERE (mt.code = 'textile' AND pg.code IN ('B'))
   OR (mt.code = 'eco_leather' AND pg.code IN ('C','C1','C2','C3'))
   OR (mt.code = 'genuine_leather' AND pg.code IN ('D','E','E1','E2','E3'));

SET FOREIGN_KEY_CHECKS = 1;
