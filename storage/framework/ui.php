<?php
declare(strict_types=1);

/**
 * Small interface dictionary. Public editorial content remains in DB translation tables;
 * this file only holds reusable UI labels.
 */
function welga_t(string $key, ?string $languageCode = null): string
{
    $languageCode ??= (string)(welga_default_language()['code'] ?? 'bg');

    $labels = [
        'bg' => [
            'home' => 'Начало', 'products' => 'Мебели', 'materials' => 'Дамаски и материали',
            'services' => 'Услуги', 'gallery' => 'Галерия', 'about' => 'За WELGA', 'contact' => 'Контакти',
            'search' => 'Търсене', 'search_placeholder' => 'Търсене по модел или име…', 'menu' => 'Меню',
            'explore_collection' => 'Разгледайте колекцията', 'new_models' => 'Последни попълнения',
            'view_all' => 'Вижте всички', 'shop_by_room' => 'Мебели по предназначение',
            'living_room' => 'Дневна', 'dining_room' => 'Трапезария', 'bedroom' => 'Спалня',
            'made_in_bulgaria' => 'Проектирано и произведено в България',
            'hero_title' => 'Собствени модели. Произведено в България.',
            'hero_text' => 'Собствена работилница за дърво и желязо, затворен производствен цикъл и мебели с внимателно подбрани материали. Решения за B2B и крайни клиенти.',
            'discover_models' => 'Разгледай мебелите', 'production' => 'Създадено във WELGA',
            'production_text' => 'Собствени разработки, работилница за дърво и желязо и затворен производствен цикъл — от идеята до готовата мебел.',
            'eu_projects' => 'Европроекти', 'learn_more' => 'Научете повече',
            'model' => 'Модел', 'category' => 'Категория', 'inquiry' => 'Запитване за продукта',
            'send_inquiry' => 'Изпрати запитване', 'request_quote' => 'Искам оферта', 'technical_info' => 'Технически данни',
            'price_list' => 'Ценова листа', 'configurations' => 'Конфигурации и размери',
            'upholstery' => 'Дамаски и материали', 'details' => 'Детайли', 'product_gallery' => 'Галерия',
            'related_models' => 'Свързани модели', 'available_materials' => 'Налични тапицерии',
            'customer_upholstery' => 'Тапицерия на клиента', 'filter' => 'Филтри', 'sort' => 'Сортиране',
            'layout' => 'Подредба', 'one_product' => '1 продукт', 'two_products' => '2 продукта',
            'show_models' => 'Покажи моделите', 'clear_filters' => 'Изчисти', 'no_products' => 'Няма намерени модели.',
            'latest_first' => 'Най-нови', 'popular' => 'Популярни', 'name_az' => 'A–Я', 'name_za' => 'Я–A', 'close' => 'Затвори',
            'view_model' => 'Виж модела', 'your_name' => 'Име', 'email' => 'Имейл', 'phone' => 'Телефон', 'message' => 'Съобщение',
            'inquiry_note' => 'Посочете модел, желана конфигурация и тапицерия, ако вече сте ги избрали.',
            'company' => 'WELGA', 'manufacturer' => 'Производител на тапицирани мебели',
            'yes' => 'Да', 'no' => 'Не', 'colors' => 'цвята', 'language' => 'Език',
            'european_production' => 'Произведено в България', 'quality_details' => 'Качество във всеки детайл',
            'service_laser_sheet' => 'Лазерно рязане на ламарина',
            'service_laser_tube' => 'Лазерно рязане на тръби и профили',
            'service_cnc_tube' => 'CNC огъване на тръби',
            'service_press_brake' => 'Абкант огъване',
        ],
        'en' => [
            'home' => 'Home', 'products' => 'Furniture', 'materials' => 'Upholstery & materials',
            'services' => 'Services', 'gallery' => 'Gallery', 'about' => 'About WELGA', 'contact' => 'Contact',
            'search' => 'Search', 'search_placeholder' => 'Search by model or name…', 'menu' => 'Menu',
            'explore_collection' => 'Explore the collection', 'new_models' => 'Latest additions',
            'view_all' => 'View all', 'shop_by_room' => 'Furniture by purpose',
            'living_room' => 'Living room', 'dining_room' => 'Dining room', 'bedroom' => 'Bedroom',
            'made_in_bulgaria' => 'Designed and made in Bulgaria',
            'hero_title' => 'Original models. Made in Bulgaria.',
            'hero_text' => 'Our own wood and metal workshop, a closed production cycle and furniture made with carefully selected materials. Solutions for B2B and private clients.',
            'discover_models' => 'Explore furniture', 'production' => 'Made at WELGA',
            'production_text' => 'Original development, our own wood and metal workshop and a closed production cycle — from concept to finished furniture.',
            'eu_projects' => 'EU projects', 'learn_more' => 'Learn more',
            'model' => 'Model', 'category' => 'Category', 'inquiry' => 'Product inquiry',
            'send_inquiry' => 'Send inquiry', 'request_quote' => 'Request a quote', 'technical_info' => 'Technical data',
            'price_list' => 'Price list', 'configurations' => 'Configurations and dimensions',
            'upholstery' => 'Upholstery & materials', 'details' => 'Details', 'product_gallery' => 'Gallery',
            'related_models' => 'Related models', 'available_materials' => 'Available upholstery',
            'customer_upholstery' => 'Customer-supplied upholstery', 'filter' => 'Filters', 'sort' => 'Sort',
            'layout' => 'Layout', 'one_product' => '1 product', 'two_products' => '2 products',
            'show_models' => 'Show models', 'clear_filters' => 'Clear', 'no_products' => 'No models found.',
            'latest_first' => 'Newest', 'popular' => 'Popular', 'name_az' => 'A–Z', 'name_za' => 'Z–A', 'close' => 'Close',
            'view_model' => 'View model', 'your_name' => 'Name', 'email' => 'Email', 'phone' => 'Phone', 'message' => 'Message',
            'inquiry_note' => 'Include the model, configuration and upholstery if you have already selected them.',
            'company' => 'WELGA', 'manufacturer' => 'Upholstered furniture manufacturer',
            'yes' => 'Yes', 'no' => 'No', 'colors' => 'colours', 'language' => 'Language',
            'european_production' => 'Made in Bulgaria', 'quality_details' => 'Quality in every detail',
            'service_laser_sheet' => 'Sheet metal laser cutting',
            'service_laser_tube' => 'Tube and profile laser cutting',
            'service_cnc_tube' => 'CNC tube bending',
            'service_press_brake' => 'Press brake bending',
        ],
        'de' => [
            'home' => 'Startseite', 'products' => 'Möbel', 'materials' => 'Polsterungen & Materialien',
            'services' => 'Leistungen', 'gallery' => 'Galerie', 'about' => 'Über WELGA', 'contact' => 'Kontakt',
            'search' => 'Suche', 'search_placeholder' => 'Nach Modell oder Name suchen…', 'menu' => 'Menü',
            'explore_collection' => 'Kollektion entdecken', 'new_models' => 'Neuheiten',
            'view_all' => 'Alle ansehen', 'shop_by_room' => 'Möbel nach Einsatzbereich',
            'living_room' => 'Wohnzimmer', 'dining_room' => 'Esszimmer', 'bedroom' => 'Schlafzimmer',
            'made_in_bulgaria' => 'Entworfen und hergestellt in Bulgarien',
            'hero_title' => 'Eigene Modelle. Hergestellt in Bulgarien.',
            'hero_text' => 'Eigene Holz- und Metallwerkstatt, geschlossener Produktionszyklus und Möbel aus sorgfältig ausgewählten Materialien. Lösungen für B2B und Privatkunden.',
            'discover_models' => 'Möbel entdecken', 'production' => 'Hergestellt bei WELGA',
            'production_text' => 'Eigene Entwicklung, Holz- und Metallwerkstatt und ein geschlossener Produktionszyklus — von der Idee bis zum fertigen Möbelstück.',
            'eu_projects' => 'EU-Projekte', 'learn_more' => 'Mehr erfahren',
            'model' => 'Modell', 'category' => 'Kategorie', 'inquiry' => 'Produktanfrage',
            'send_inquiry' => 'Anfrage senden', 'request_quote' => 'Angebot anfordern', 'technical_info' => 'Technische Daten',
            'price_list' => 'Preisliste', 'configurations' => 'Konfigurationen und Abmessungen',
            'upholstery' => 'Polsterungen & Materialien', 'details' => 'Details', 'product_gallery' => 'Galerie',
            'related_models' => 'Verwandte Modelle', 'available_materials' => 'Verfügbare Polsterungen',
            'customer_upholstery' => 'Polsterstoff des Kunden', 'filter' => 'Filter', 'sort' => 'Sortieren',
            'layout' => 'Darstellung', 'one_product' => '1 Produkt', 'two_products' => '2 Produkte',
            'show_models' => 'Modelle anzeigen', 'clear_filters' => 'Zurücksetzen', 'no_products' => 'Keine Modelle gefunden.',
            'latest_first' => 'Neueste', 'popular' => 'Beliebt', 'name_az' => 'A–Z', 'name_za' => 'Z–A', 'close' => 'Schließen',
            'view_model' => 'Modell ansehen', 'your_name' => 'Name', 'email' => 'E-Mail', 'phone' => 'Telefon', 'message' => 'Nachricht',
            'inquiry_note' => 'Geben Sie Modell, Konfiguration und Polsterung an, falls bereits ausgewählt.',
            'company' => 'WELGA', 'manufacturer' => 'Hersteller von Polstermöbeln',
            'yes' => 'Ja', 'no' => 'Nein', 'colors' => 'Farben', 'language' => 'Sprache',
            'european_production' => 'Hergestellt in Bulgarien', 'quality_details' => 'Qualität in jedem Detail',
            'service_laser_sheet' => 'Laserschneiden von Blech',
            'service_laser_tube' => 'Laserschneiden von Rohren und Profilen',
            'service_cnc_tube' => 'CNC-Rohrbiegen',
            'service_press_brake' => 'Abkantbiegen',
        ],
    ];

    return $labels[$languageCode][$key] ?? $labels['bg'][$key] ?? $key;
}

function welga_icon(string $name, string $class = '', ?string $label = null): string
{
    $name = preg_replace('/[^a-z0-9-]/i', '', $name) ?: 'spark';
    $class = trim('welga-icon ' . preg_replace('/[^a-z0-9 _-]/i', '', $class));
    $aria = $label !== null && $label !== ''
        ? ' role="img" aria-label="' . welga_escape($label) . '"'
        : ' aria-hidden="true"';

    return '<svg class="' . welga_escape($class) . '"' . $aria . '><use href="/assets/icons/welga-icons.svg#icon-' . welga_escape($name) . '"></use></svg>';
}

function welga_feature_icon_name(string $label): string
{
    $value = function_exists('mb_strtolower') ? mb_strtolower($label, 'UTF-8') : strtolower($label);
    $map = [
        'sleep' => ['сън', 'sleep', 'schlaf'],
        'modular' => ['модул', 'modular', 'modul'],
        'relax' => ['релакс', 'relax'],
        'armchair' => ['фотьойл', 'armchair', 'sessel'],
        'cushion' => ['възглав', 'cushion', 'kissen'],
        'layers' => ['материал', 'material', 'пяна', 'foam', 'polster'],
        'wood' => ['дърв', 'wood', 'holz'],
        'metal' => ['метал', 'metal', 'хром', 'inox', 'stahl'],
        'leaf' => ['стил', 'design', 'дизайн', 'style'],
        'mechanism' => ['механиз', 'mechanism', 'mechanik'],
        'storage' => ['ракла', 'storage', 'stauraum'],
        'upholstery' => ['тапиц', 'upholstery', 'дамас', 'stoff', 'leder'],
    ];

    foreach ($map as $icon => $needles) {
        foreach ($needles as $needle) {
            if (str_contains($value, $needle)) {
                return $icon;
            }
        }
    }

    return 'spark';
}

function welga_product_display_title(string $name, string $model): string
{
    $name = trim($name);
    $model = trim($model);
    if ($model === '' || stripos($name, $model) !== false) {
        return $name;
    }
    return trim($name . ' ' . $model);
}
