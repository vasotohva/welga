<?php
declare(strict_types=1);

$languageCode = (string)($language['code'] ?? 'bg');
$pageKey = (string)($pageKey ?? 'page');

$content = [
    'bg' => [
        'materials' => ['Тапицерии', 'Изграждаме динамичната библиотека с текстил, еко кожа, естествена кожа, колекции и цветове. В продуктовите страници изборът ще се показва автоматично според конкретния модел.'],
        'services' => ['Производство и услуги', 'Подготвяме отделните страници за производствените възможности на WELGA — лазерно рязане, обработка и огъване на метал, както и запитвания с технически файлове.'],
        'gallery' => ['Галерия', 'Галерията ще събира продуктови детайли, интериорни кадри и визуални истории от колекциите на WELGA в по-свободен editorial формат.'],
        'about' => ['За WELGA', 'Тази страница ще представя WELGA като български производител със собствена работилница, собствена разработка на модели и затворен производствен цикъл.'],
        'contact' => ['Контакти', 'Контактната страница и формите за запитване се довършват. Финалната версия ще включва директни контакти и подходящи форми според типа запитване.'],
        'projects' => ['Европейски проекти', 'Модулът за текущи и завършени европейски проекти се изгражда като структурирана секция с периоди, програми, документи и резултати.'],
    ],
    'en' => [
        'materials' => ['Upholstery', 'We are building the dynamic textile, eco leather and genuine leather library with collections and colours. Product pages will resolve the available selection automatically for each model.'],
        'services' => ['Manufacturing and services', 'Dedicated pages for WELGA manufacturing capabilities are being prepared, including laser cutting, metal processing and bending, plus technical-file inquiries.'],
        'gallery' => ['Gallery', 'The gallery will bring together product details, interior imagery and visual stories from WELGA collections in a more editorial format.'],
        'about' => ['About WELGA', 'This page will present WELGA as a Bulgarian manufacturer with its own workshop, in-house model development and a closed production cycle.'],
        'contact' => ['Contact', 'The contact page and inquiry forms are being completed. The final version will provide direct contacts and forms tailored to the type of request.'],
        'projects' => ['European projects', 'The current and completed European projects module is being built as a structured section with periods, programmes, documents and results.'],
    ],
    'de' => [
        'materials' => ['Polsterungen', 'Wir bauen die dynamische Bibliothek für Textilien, Kunstleder und Echtleder mit Kollektionen und Farben auf. Die Produktseiten zeigen die jeweils verfügbaren Optionen automatisch pro Modell.'],
        'services' => ['Produktion und Leistungen', 'Eigene Seiten für die Fertigungsmöglichkeiten von WELGA werden vorbereitet, darunter Laserschneiden, Metallbearbeitung und Biegen sowie Anfragen mit technischen Dateien.'],
        'gallery' => ['Galerie', 'Die Galerie verbindet Produktdetails, Interieuraufnahmen und visuelle Geschichten aus den WELGA Kollektionen in einem redaktionellen Format.'],
        'about' => ['Über WELGA', 'Diese Seite stellt WELGA als bulgarischen Hersteller mit eigener Werkstatt, eigener Modellentwicklung und geschlossenem Produktionszyklus vor.'],
        'contact' => ['Kontakt', 'Die Kontaktseite und Anfrageformulare werden fertiggestellt. Die finale Version bietet direkte Kontakte und passende Formulare je nach Anfrageart.'],
        'projects' => ['Europäische Projekte', 'Das Modul für laufende und abgeschlossene europäische Projekte entsteht als strukturierter Bereich mit Zeiträumen, Programmen, Dokumenten und Ergebnissen.'],
    ],
];

$page = $content[$languageCode][$pageKey] ?? $content['bg'][$pageKey] ?? ['WELGA', ''];
?>
<section class="preview-page shell-wide">
  <div class="preview-page__number">WELGA / DEV</div>
  <div class="preview-page__content reveal">
    <p class="kicker"><?= welga_escape($page[0]) ?></p>
    <h1><?= welga_escape($page[0]) ?></h1>
    <p><?= welga_escape($page[1]) ?></p>
    <div class="preview-page__status">
      <span></span>
      <strong><?= $languageCode === 'bg' ? 'Секцията е в процес на изработка' : ($languageCode === 'de' ? 'Dieser Bereich wird derzeit entwickelt' : 'This section is currently being developed') ?></strong>
    </div>
    <a class="button button--dark" href="<?= welga_escape(welga_url('', $languageCode)) ?>"><?= $languageCode === 'bg' ? 'Към началната страница' : ($languageCode === 'de' ? 'Zur Startseite' : 'Back to homepage') ?><span>↗</span></a>
  </div>
</section>
