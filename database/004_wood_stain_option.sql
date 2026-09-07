-- WELGA / ETKO Catalog CMS
-- Migration 004: legacy wood-stain gallery -> structured product option.
--
-- The legacy Journal setup contains 11 wood-stain swatches and a product tab
-- assigned to 35 legacy products. Only products actually migrated into the new
-- catalogue receive this option.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO catalog_options (code, input_type, status, sort_order)
VALUES ('wood_stain', 'swatch', 1, 40)
ON DUPLICATE KEY UPDATE input_type = VALUES(input_type), status = VALUES(status), sort_order = VALUES(sort_order);

SET @wood_stain_option_id = (SELECT option_id FROM catalog_options WHERE code = 'wood_stain' LIMIT 1);

INSERT IGNORE INTO catalog_option_translations (option_id, language_id, name) VALUES
(@wood_stain_option_id, 1, 'Байц за дърво'),
(@wood_stain_option_id, 2, 'Wood stain'),
(@wood_stain_option_id, 3, 'Holzbeize');

INSERT INTO catalog_option_values (option_id, code, media_id, status, sort_order) VALUES
(@wood_stain_option_id, '01-natural',      NULL, 1, 10),
(@wood_stain_option_id, '02-mahogany',     NULL, 1, 20),
(@wood_stain_option_id, '03-golden-oak',   NULL, 1, 30),
(@wood_stain_option_id, '04-black',        NULL, 1, 40),
(@wood_stain_option_id, '05-standard-2',   NULL, 1, 50),
(@wood_stain_option_id, '06-hazelnut',     NULL, 1, 60),
(@wood_stain_option_id, '07-wenge',        NULL, 1, 70),
(@wood_stain_option_id, '08-cherry',       NULL, 1, 80),
(@wood_stain_option_id, '09-standard-1',   NULL, 1, 90),
(@wood_stain_option_id, '10-walnut',       NULL, 1, 100),
(@wood_stain_option_id, '11-1003',         NULL, 1, 110)
ON DUPLICATE KEY UPDATE status = VALUES(status), sort_order = VALUES(sort_order);

INSERT IGNORE INTO catalog_option_value_translations (option_value_id, language_id, name)
SELECT option_value_id, 1,
       CASE code
           WHEN '01-natural' THEN 'Натурал'
           WHEN '02-mahogany' THEN 'Махагон'
           WHEN '03-golden-oak' THEN 'Златен дъб'
           WHEN '04-black' THEN 'Черен'
           WHEN '05-standard-2' THEN 'Стандарт 2'
           WHEN '06-hazelnut' THEN 'Лешник'
           WHEN '07-wenge' THEN 'Венге'
           WHEN '08-cherry' THEN 'Череша'
           WHEN '09-standard-1' THEN 'Стандарт 1'
           WHEN '10-walnut' THEN 'Орех'
           WHEN '11-1003' THEN '1003'
       END
FROM catalog_option_values WHERE option_id = @wood_stain_option_id;

INSERT IGNORE INTO catalog_option_value_translations (option_value_id, language_id, name)
SELECT option_value_id, 2,
       CASE code
           WHEN '01-natural' THEN 'Natural'
           WHEN '02-mahogany' THEN 'Mahogany'
           WHEN '03-golden-oak' THEN 'Golden Oak'
           WHEN '04-black' THEN 'Black'
           WHEN '05-standard-2' THEN 'Standard 2'
           WHEN '06-hazelnut' THEN 'Hazelnut'
           WHEN '07-wenge' THEN 'Wenge'
           WHEN '08-cherry' THEN 'Cherry'
           WHEN '09-standard-1' THEN 'Standard 1'
           WHEN '10-walnut' THEN 'Walnut'
           WHEN '11-1003' THEN '1003'
       END
FROM catalog_option_values WHERE option_id = @wood_stain_option_id;

INSERT IGNORE INTO catalog_option_value_translations (option_value_id, language_id, name)
SELECT option_value_id, 3,
       CASE code
           WHEN '01-natural' THEN 'Natur'
           WHEN '02-mahogany' THEN 'Mahagoni'
           WHEN '03-golden-oak' THEN 'Goldeiche'
           WHEN '04-black' THEN 'Schwarz'
           WHEN '05-standard-2' THEN 'Standard 2'
           WHEN '06-hazelnut' THEN 'Haselnuss'
           WHEN '07-wenge' THEN 'Wenge'
           WHEN '08-cherry' THEN 'Kirsche'
           WHEN '09-standard-1' THEN 'Standard 1'
           WHEN '10-walnut' THEN 'Walnuss'
           WHEN '11-1003' THEN '1003'
       END
FROM catalog_option_values WHERE option_id = @wood_stain_option_id;

-- Legacy Journal module 323 product assignments.
INSERT IGNORE INTO catalog_product_options (product_id, option_id, required, sort_order)
SELECT product_id, @wood_stain_option_id, 0, 40
FROM catalog_products
WHERE legacy_product_id IN (
    56,58,62,63,64,65,67,69,70,71,72,73,74,75,77,78,89,91,94,95,96,98,99,101,109,
    116,117,118,119,120,122,123,130,131,134
);

INSERT IGNORE INTO catalog_product_option_values (product_option_id, option_value_id, status, sort_order)
SELECT po.product_option_id, ov.option_value_id, 1, ov.sort_order
FROM catalog_product_options po
JOIN catalog_option_values ov ON ov.option_id = po.option_id
WHERE po.option_id = @wood_stain_option_id;

-- Link swatch media after media migration by exact legacy paths.
UPDATE catalog_option_values ov
JOIN media m ON m.legacy_path = CONCAT('data/Wood Stain/',
    CASE ov.code
        WHEN '01-natural' THEN '1_Natural.jpg'
        WHEN '02-mahogany' THEN '2_Mahogany.jpg'
        WHEN '03-golden-oak' THEN '3_Golden Oak.jpg'
        WHEN '04-black' THEN '4_Black.jpg'
        WHEN '05-standard-2' THEN '5_Standart2.jpg'
        WHEN '06-hazelnut' THEN '6_Nut.jpg'
        WHEN '07-wenge' THEN '7_Wenge.jpg'
        WHEN '08-cherry' THEN '8_Cherry.jpg'
        WHEN '09-standard-1' THEN '9_Standart1.jpg'
        WHEN '10-walnut' THEN '10_Walnut.jpg'
        WHEN '11-1003' THEN '11_1003.jpg'
    END)
SET ov.media_id = m.media_id
WHERE ov.option_id = @wood_stain_option_id;

SET FOREIGN_KEY_CHECKS = 1;
