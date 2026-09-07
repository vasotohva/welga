-- WELGA / ETKO Catalog CMS
-- Legacy taxonomy seed: categories + filter vocabulary.
--
-- Legacy IDs are kept only for migration traceability. Public labels are cleaned
-- and German is added from day one. Product/category and product/filter
-- relations are imported by the catalogue importer after products exist.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

SET @bg = (SELECT language_id FROM languages WHERE code = 'bg' LIMIT 1);
SET @en = (SELECT language_id FROM languages WHERE code = 'en' LIMIT 1);
SET @de = (SELECT language_id FROM languages WHERE code = 'de' LIMIT 1);

-- ---------------------------------------------------------------------------
-- Categories
-- ---------------------------------------------------------------------------
INSERT INTO catalog_categories (legacy_category_id, parent_id, status, sort_order) VALUES
(17, NULL, 1, 3),
(18, NULL, 1, 7),
(20, NULL, 1, 4),
(24, NULL, 1, 5),
(25, NULL, 1, 1),
(33, NULL, 1, 1),
(57, NULL, 1, 2),
(61, NULL, 1, 0),
(62, NULL, 1, 0),
(63, NULL, 1, 0),
(64, NULL, 1, 0)
ON DUPLICATE KEY UPDATE status = VALUES(status), sort_order = VALUES(sort_order);

-- Restore the legacy category tree after every category has an ETKO id.
UPDATE catalog_categories c
LEFT JOIN catalog_categories p ON p.legacy_category_id =
    CASE c.legacy_category_id
        WHEN 17 THEN 61
        WHEN 18 THEN 63
        WHEN 20 THEN 61
        WHEN 24 THEN 62
        WHEN 25 THEN 62
        WHEN 33 THEN 61
        WHEN 57 THEN 61
        WHEN 61 THEN 62
        WHEN 63 THEN 62
        WHEN 64 THEN 63
        ELSE NULL
    END
SET c.parent_id = p.category_id
WHERE c.legacy_category_id IN (17,18,20,24,25,33,57,61,62,63,64);

-- BG
INSERT INTO catalog_category_translations (category_id, language_id, name, slug)
SELECT category_id, @bg,
       CASE legacy_category_id
           WHEN 17 THEN 'Фотьойли'
           WHEN 18 THEN 'Домашно кино'
           WHEN 20 THEN 'Табуретки'
           WHEN 24 THEN 'Столове и маси'
           WHEN 25 THEN 'Спални'
           WHEN 33 THEN 'Ъглови дивани'
           WHEN 57 THEN 'Канапета'
           WHEN 61 THEN 'Мебели за дневна'
           WHEN 62 THEN 'Продукти'
           WHEN 63 THEN 'Релакс фотьойли и домашно кино'
           WHEN 64 THEN 'Релакс фотьойли'
       END,
       CASE legacy_category_id
           WHEN 17 THEN 'fotioili'
           WHEN 18 THEN 'domashno-kino'
           WHEN 20 THEN 'taburetki'
           WHEN 24 THEN 'stolove'
           WHEN 25 THEN 'spalni'
           WHEN 33 THEN 'aglovi-divani'
           WHEN 57 THEN 'kanapeta'
           WHEN 61 THEN 'mebeli-za-dnevna'
           WHEN 62 THEN 'produkti'
           WHEN 63 THEN 'relaks-fotioili-i-domashno-kino'
           WHEN 64 THEN 'relaks-fotioili'
       END
FROM catalog_categories
WHERE legacy_category_id IN (17,18,20,24,25,33,57,61,62,63,64)
ON DUPLICATE KEY UPDATE name = VALUES(name), slug = VALUES(slug);

-- EN: fix legacy naming inconsistencies such as category 24 = "Chairs" although
-- it contains both chairs and tables.
INSERT INTO catalog_category_translations (category_id, language_id, name, slug)
SELECT category_id, @en,
       CASE legacy_category_id
           WHEN 17 THEN 'Armchairs'
           WHEN 18 THEN 'Home cinema'
           WHEN 20 THEN 'Stools'
           WHEN 24 THEN 'Chairs and tables'
           WHEN 25 THEN 'Bedrooms'
           WHEN 33 THEN 'Corner sofas'
           WHEN 57 THEN 'Sofas'
           WHEN 61 THEN 'Living room furniture'
           WHEN 62 THEN 'Products'
           WHEN 63 THEN 'Relax armchairs and home cinema'
           WHEN 64 THEN 'Relax armchairs'
       END,
       CASE legacy_category_id
           WHEN 17 THEN 'armchairs'
           WHEN 18 THEN 'home-cinema'
           WHEN 20 THEN 'stools'
           WHEN 24 THEN 'chairs-and-tables'
           WHEN 25 THEN 'bedrooms'
           WHEN 33 THEN 'corner-sofas'
           WHEN 57 THEN 'sofas'
           WHEN 61 THEN 'living-room-furniture'
           WHEN 62 THEN 'products'
           WHEN 63 THEN 'relax-armchairs-and-home-cinema'
           WHEN 64 THEN 'relax-armchairs'
       END
FROM catalog_categories
WHERE legacy_category_id IN (17,18,20,24,25,33,57,61,62,63,64)
ON DUPLICATE KEY UPDATE name = VALUES(name), slug = VALUES(slug);

-- DE
INSERT INTO catalog_category_translations (category_id, language_id, name, slug)
SELECT category_id, @de,
       CASE legacy_category_id
           WHEN 17 THEN 'Sessel'
           WHEN 18 THEN 'Heimkino'
           WHEN 20 THEN 'Hocker'
           WHEN 24 THEN 'Stühle und Tische'
           WHEN 25 THEN 'Schlafzimmer'
           WHEN 33 THEN 'Ecksofas'
           WHEN 57 THEN 'Sofas'
           WHEN 61 THEN 'Wohnzimmermöbel'
           WHEN 62 THEN 'Produkte'
           WHEN 63 THEN 'Relaxsessel und Heimkino'
           WHEN 64 THEN 'Relaxsessel'
       END,
       CASE legacy_category_id
           WHEN 17 THEN 'sessel'
           WHEN 18 THEN 'heimkino'
           WHEN 20 THEN 'hocker'
           WHEN 24 THEN 'stuehle-und-tische'
           WHEN 25 THEN 'schlafzimmer'
           WHEN 33 THEN 'ecksofas'
           WHEN 57 THEN 'sofas'
           WHEN 61 THEN 'wohnzimmermoebel'
           WHEN 62 THEN 'produkte'
           WHEN 63 THEN 'relaxsessel-und-heimkino'
           WHEN 64 THEN 'relaxsessel'
       END
FROM catalog_categories
WHERE legacy_category_id IN (17,18,20,24,25,33,57,61,62,63,64)
ON DUPLICATE KEY UPDATE name = VALUES(name), slug = VALUES(slug);

-- ---------------------------------------------------------------------------
-- Filter groups
-- ---------------------------------------------------------------------------
INSERT INTO catalog_filter_groups (legacy_filter_group_id, status, sort_order) VALUES
(1, 1, 10),
(2, 1, 20)
ON DUPLICATE KEY UPDATE status = VALUES(status), sort_order = VALUES(sort_order);

INSERT INTO catalog_filter_group_translations (filter_group_id, language_id, name)
SELECT filter_group_id, @bg, CASE legacy_filter_group_id WHEN 1 THEN 'Стил' WHEN 2 THEN 'Предимства' END
FROM catalog_filter_groups WHERE legacy_filter_group_id IN (1,2)
ON DUPLICATE KEY UPDATE name = VALUES(name);
INSERT INTO catalog_filter_group_translations (filter_group_id, language_id, name)
SELECT filter_group_id, @en, CASE legacy_filter_group_id WHEN 1 THEN 'Style' WHEN 2 THEN 'Features' END
FROM catalog_filter_groups WHERE legacy_filter_group_id IN (1,2)
ON DUPLICATE KEY UPDATE name = VALUES(name);
INSERT INTO catalog_filter_group_translations (filter_group_id, language_id, name)
SELECT filter_group_id, @de, CASE legacy_filter_group_id WHEN 1 THEN 'Stil' WHEN 2 THEN 'Merkmale' END
FROM catalog_filter_groups WHERE legacy_filter_group_id IN (1,2)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- ---------------------------------------------------------------------------
-- Filters
-- ---------------------------------------------------------------------------
INSERT INTO catalog_filters (filter_group_id, legacy_filter_id, status, sort_order)
SELECT g.filter_group_id, x.legacy_filter_id, 1, x.sort_order
FROM catalog_filter_groups g
JOIN (
    SELECT 1 legacy_filter_id, 1 legacy_group_id, 1 sort_order UNION ALL
    SELECT 11,1,2 UNION ALL
    SELECT 3,2,2 UNION ALL
    SELECT 4,2,3 UNION ALL
    SELECT 5,2,4 UNION ALL
    SELECT 6,2,5 UNION ALL
    SELECT 7,2,6 UNION ALL
    SELECT 8,2,7 UNION ALL
    SELECT 9,2,8 UNION ALL
    SELECT 10,2,9 UNION ALL
    SELECT 12,2,10 UNION ALL
    SELECT 13,2,11 UNION ALL
    SELECT 14,2,12 UNION ALL
    SELECT 15,2,13 UNION ALL
    SELECT 16,2,14 UNION ALL
    SELECT 17,2,15 UNION ALL
    SELECT 18,2,16 UNION ALL
    SELECT 19,2,17 UNION ALL
    SELECT 20,2,18
) x ON x.legacy_group_id = g.legacy_filter_group_id
ON DUPLICATE KEY UPDATE filter_group_id = VALUES(filter_group_id), status = VALUES(status), sort_order = VALUES(sort_order);

-- BG cleaned terminology.
INSERT INTO catalog_filter_translations (filter_id, language_id, name)
SELECT filter_id, @bg,
       CASE legacy_filter_id
           WHEN 1 THEN 'Атрактивен дизайн'
           WHEN 3 THEN 'Седалка с пружини'
           WHEN 4 THEN 'Ракла за съхранение'
           WHEN 5 THEN 'Тапицерия с двоен лицев шев'
           WHEN 6 THEN 'Частично сваляема тапицерия'
           WHEN 7 THEN 'Високоеластична пяна'
           WHEN 8 THEN 'Силиконов пълнеж'
           WHEN 9 THEN 'Крака от масивна дървесина'
           WHEN 10 THEN 'Механизъм за сън – опция'
           WHEN 11 THEN 'Класически дизайн'
           WHEN 12 THEN 'Възможност за сън с допълнителен модул'
           WHEN 13 THEN 'Модулна система'
           WHEN 14 THEN 'Функция за сън'
           WHEN 15 THEN 'Изцяло сваляема тапицерия'
           WHEN 16 THEN 'Хромирани детайли'
           WHEN 17 THEN 'Двулицеви възглавници'
           WHEN 18 THEN 'Крака от неръждаема стомана'
           WHEN 19 THEN 'Електрически релакс механизъм – опция'
           WHEN 20 THEN 'Ръчен релакс механизъм – опция'
       END
FROM catalog_filters WHERE legacy_filter_id IN (1,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- EN cleaned terminology.
INSERT INTO catalog_filter_translations (filter_id, language_id, name)
SELECT filter_id, @en,
       CASE legacy_filter_id
           WHEN 1 THEN 'Attractive design'
           WHEN 3 THEN 'Sprung seat'
           WHEN 4 THEN 'Storage compartment'
           WHEN 5 THEN 'Double topstitching'
           WHEN 6 THEN 'Partially removable upholstery'
           WHEN 7 THEN 'High-resilience foam'
           WHEN 8 THEN 'Silicone filling'
           WHEN 9 THEN 'Solid wood legs'
           WHEN 10 THEN 'Sleep mechanism – optional'
           WHEN 11 THEN 'Classic design'
           WHEN 12 THEN 'Sleep option with additional module'
           WHEN 13 THEN 'Modular system'
           WHEN 14 THEN 'Sleep function'
           WHEN 15 THEN 'Fully removable upholstery'
           WHEN 16 THEN 'Chrome-plated details'
           WHEN 17 THEN 'Reversible cushions'
           WHEN 18 THEN 'Stainless-steel legs'
           WHEN 19 THEN 'Electric relax mechanism – optional'
           WHEN 20 THEN 'Manual relax mechanism – optional'
       END
FROM catalog_filters WHERE legacy_filter_id IN (1,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- DE
INSERT INTO catalog_filter_translations (filter_id, language_id, name)
SELECT filter_id, @de,
       CASE legacy_filter_id
           WHEN 1 THEN 'Attraktives Design'
           WHEN 3 THEN 'Sitz mit Federung'
           WHEN 4 THEN 'Stauraum'
           WHEN 5 THEN 'Polsterung mit Doppelnaht'
           WHEN 6 THEN 'Teilweise abnehmbarer Bezug'
           WHEN 7 THEN 'Hochelastischer Schaumstoff'
           WHEN 8 THEN 'Silikonfüllung'
           WHEN 9 THEN 'Massivholzfüße'
           WHEN 10 THEN 'Schlafmechanismus – optional'
           WHEN 11 THEN 'Klassisches Design'
           WHEN 12 THEN 'Schlafmöglichkeit mit Zusatzmodul'
           WHEN 13 THEN 'Modulares Möbelsystem'
           WHEN 14 THEN 'Mit Schlaffunktion'
           WHEN 15 THEN 'Vollständig abnehmbarer Bezug'
           WHEN 16 THEN 'Verchromte Details'
           WHEN 17 THEN 'Beidseitig nutzbare Kissen'
           WHEN 18 THEN 'Edelstahlfüße'
           WHEN 19 THEN 'Elektrischer Relaxmechanismus – optional'
           WHEN 20 THEN 'Manueller Relaxmechanismus – optional'
       END
FROM catalog_filters WHERE legacy_filter_id IN (1,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20)
ON DUPLICATE KEY UPDATE name = VALUES(name);

SET FOREIGN_KEY_CHECKS = 1;
