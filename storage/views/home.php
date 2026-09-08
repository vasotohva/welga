<?php
declare(strict_types=1);

$languageCode = (string)($language['code'] ?? 'bg');
$products = $products ?? [];
$categories = $categories ?? [];
$gallery = $gallery ?? [];
$services = $services ?? [];
$projects = $projects ?? [];
$heroProduct = $products[0] ?? null;

$productHref = static function (?array $product) use ($languageCode): string {
    if ($product === null) {
        return '#';
    }
    $route = trim((string)($product['route_path'] ?? ''), '/');
    return $route !== '' ? welga_url($route, $languageCode) : '#';
};

$roomSlugs = [
    'bg' => ['mebeli-za-dnevna', 'stolove', 'spalni'],
    'en' => ['living-room-furniture', 'chairs-and-tables', 'bedrooms'],
    'de' => ['wohnzimmermoebel', 'stuehle-und-tische', 'schlafzimmer'],
];
$roomCategories = [];
foreach ($roomSlugs[$languageCode] ?? $roomSlugs['bg'] as $wantedSlug) {
    foreach ($categories as $category) {
        if (($category['slug'] ?? '') === $wantedSlug) {
            $roomCategories[] = $category;
            break;
        }
    }
}
if (count($roomCategories) < 3) {
    foreach ($categories as $category) {
        if (count($roomCategories) >= 3) {
            break;
        }
        if (!in_array($category, $roomCategories, true) && !empty($category['route_path'])) {
            $roomCategories[] = $category;
        }
    }
}
?>

<section class="home-hero">
  <div class="home-hero__copy shell-wide reveal">
    <p class="kicker"><?= welga_escape(welga_t('made_in_bulgaria', $languageCode)) ?></p>
    <h1><?= welga_escape(welga_t('hero_title', $languageCode)) ?></h1>
    <div class="home-hero__intro">
      <p><?= welga_escape(welga_t('hero_text', $languageCode)) ?></p>
      <a class="text-link" href="#new-models"><?= welga_escape(welga_t('discover_models', $languageCode)) ?><span>↘</span></a>
    </div>
  </div>

  <a class="home-hero__media reveal" href="<?= welga_escape($productHref($heroProduct)) ?>">
    <?php if (!empty($heroProduct['image_path'])): ?>
      <img src="/<?= welga_escape(ltrim((string)$heroProduct['image_path'], '/')) ?>" alt="<?= welga_escape((string)($heroProduct['name'] ?? 'WELGA')) ?>" fetchpriority="high" decoding="async">
    <?php else: ?>
      <span class="media-placeholder media-placeholder--hero"></span>
    <?php endif; ?>
    <?php if ($heroProduct !== null): ?>
      <span class="media-label"><b><?= welga_escape((string)$heroProduct['model']) ?></b><span><?= welga_escape((string)$heroProduct['name']) ?></span></span>
    <?php endif; ?>
  </a>
</section>

<section class="editorial-grid shell-wide" aria-label="WELGA furniture selection">
  <?php foreach (array_slice($products, 1, 3) as $index => $product): ?>
    <a class="editorial-card editorial-card--<?= $index + 1 ?> reveal" href="<?= welga_escape($productHref($product)) ?>">
      <span class="editorial-card__media">
        <?php if (!empty($product['image_path'])): ?><img src="/<?= welga_escape(ltrim((string)$product['image_path'], '/')) ?>" alt="<?= welga_escape((string)$product['name']) ?>" loading="lazy"><?php else: ?><span class="media-placeholder"></span><?php endif; ?>
      </span>
      <span class="editorial-card__meta"><b><?= welga_escape((string)$product['model']) ?></b><span><?= welga_escape((string)$product['name']) ?></span><i>↗</i></span>
    </a>
  <?php endforeach; ?>
</section>

<section class="section section--products shell-wide" id="new-models">
  <div class="section-heading reveal">
    <div><p class="section-number">01</p><h2><?= welga_escape(welga_t('new_models', $languageCode)) ?></h2></div>
    <p><?= welga_escape(welga_t('made_in_bulgaria', $languageCode)) ?></p>
  </div>
  <div class="product-grid product-grid--home">
    <?php foreach ($products as $cardProduct): ?>
      <div class="reveal"><?php require WELGA_STORAGE . '/views/partials/product-card.php'; ?></div>
    <?php endforeach; ?>
  </div>
</section>

<section class="section section--rooms shell-wide">
  <div class="section-heading reveal">
    <div><p class="section-number">02</p><h2><?= welga_escape(welga_t('shop_by_room', $languageCode)) ?></h2></div>
    <p>WELGA / Interior</p>
  </div>
  <div class="room-grid">
    <?php foreach (array_slice($roomCategories, 0, 3) as $index => $category): ?>
      <?php
        $route = trim((string)($category['route_path'] ?? ''), '/');
        $href = $route !== '' ? welga_url($route, $languageCode) : '#';
        $fallbackImage = $products[$index + 1]['image_path'] ?? $products[$index]['image_path'] ?? '';
        $roomImage = $category['image_path'] ?: $fallbackImage;
      ?>
      <a class="room-card reveal" href="<?= welga_escape($href) ?>">
        <span class="room-card__image"><?php if ($roomImage): ?><img src="/<?= welga_escape(ltrim((string)$roomImage, '/')) ?>" alt="<?= welga_escape((string)$category['name']) ?>" loading="lazy"><?php else: ?><span class="media-placeholder"></span><?php endif; ?></span>
        <span class="room-card__body"><span>0<?= $index + 1 ?></span><h3><?= welga_escape((string)$category['name']) ?></h3><i>↗</i></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="material-story">
  <div class="material-story__media reveal">
    <?php $storyImage = $products[4]['image_path'] ?? $products[0]['image_path'] ?? ''; ?>
    <?php if ($storyImage): ?><img src="/<?= welga_escape(ltrim((string)$storyImage, '/')) ?>" alt="WELGA upholstery" loading="lazy"><?php else: ?><span class="media-placeholder media-placeholder--hero"></span><?php endif; ?>
  </div>
  <div class="material-story__content reveal">
    <p class="section-number">03</p>
    <p class="kicker">Materials / WELGA</p>
    <h2><?= welga_escape(welga_t('materials', $languageCode)) ?></h2>
    <p><?= $languageCode === 'bg' ? 'Текстил, еко кожа и естествена кожа в динамична библиотека от колекции и цветове. Изборът се показва според конкретния модел и неговата ценова група.' : ($languageCode === 'de' ? 'Textil, Kunstleder und Echtleder in einer dynamischen Bibliothek aus Kollektionen und Farben. Die Auswahl wird passend zum jeweiligen Modell angezeigt.' : 'Textile, eco leather and genuine leather in a dynamic library of collections and colours. Availability is resolved for each specific model.') ?></p>
    <a class="button button--dark" href="<?= welga_escape(welga_url('tapicerii', $languageCode)) ?>"><?= welga_escape(welga_t('explore_collection', $languageCode)) ?><span>↗</span></a>
  </div>
</section>

<section class="section gallery-module shell-wide">
  <div class="section-heading reveal">
    <div><p class="section-number">04</p><h2><?= welga_escape(welga_t('gallery', $languageCode)) ?></h2></div>
    <a class="text-link" href="<?= welga_escape(welga_url('galeria', $languageCode)) ?>"><?= welga_escape(welga_t('view_all', $languageCode)) ?><span>↗</span></a>
  </div>
  <div class="gallery-mosaic">
    <?php $galleryItems = $gallery !== [] ? $gallery : array_slice($products, 0, 6); ?>
    <?php foreach (array_slice($galleryItems, 0, 7) as $index => $item): ?>
      <?php $image = $item['path'] ?? $item['image_path'] ?? ''; $route = trim((string)($item['route_path'] ?? ''), '/'); ?>
      <a class="gallery-tile gallery-tile--<?= ($index % 5) + 1 ?> reveal" href="<?= $route !== '' ? welga_escape(welga_url($route, $languageCode)) : '#' ?>">
        <?php if ($image): ?><img src="/<?= welga_escape(ltrim((string)$image, '/')) ?>" alt="<?= welga_escape((string)($item['alt_text'] ?? $item['product_name'] ?? $item['name'] ?? 'WELGA')) ?>" loading="lazy"><?php else: ?><span class="media-placeholder"></span><?php endif; ?>
        <?php if (!empty($item['model'])): ?><span><?= welga_escape((string)$item['model']) ?></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="production-block" id="services">
  <div class="shell-wide production-block__grid">
    <div class="production-block__intro reveal">
      <p class="section-number">05</p>
      <p class="kicker">WELGA / Manufacturing</p>
      <h2><?= welga_escape(welga_t('production', $languageCode)) ?></h2>
      <p><?= welga_escape(welga_t('production_text', $languageCode)) ?></p>
      <a class="button button--light" href="<?= welga_escape(welga_url('welga-uslugi', $languageCode)) ?>"><?= welga_escape(welga_t('learn_more', $languageCode)) ?><span>↗</span></a>
    </div>
    <div class="service-list reveal">
      <?php foreach ($services as $index => $service): ?>
        <article class="service-row"><span>0<?= $index + 1 ?></span><div><h3><?= welga_escape((string)$service['title']) ?></h3><?php if (!empty($service['short_description'])): ?><p><?= welga_escape((string)$service['short_description']) ?></p><?php endif; ?></div><i>↗</i></article>
      <?php endforeach; ?>
      <?php if ($services === []): ?>
        <?php foreach (['Лазерно рязане на ламарина', 'Лазерно рязане на тръби и профили', 'CNC огъване на тръби', 'Абкант огъване'] as $index => $label): ?>
          <article class="service-row"><span>0<?= $index + 1 ?></span><div><h3><?= welga_escape($label) ?></h3></div><i>↗</i></article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section projects-section shell-wide" id="projects">
  <div class="section-heading reveal">
    <div><p class="section-number">06</p><h2><?= welga_escape(welga_t('eu_projects', $languageCode)) ?></h2></div>
    <a class="text-link" href="<?= welga_escape(welga_url('tekushti-evroproekti', $languageCode)) ?>"><?= welga_escape(welga_t('learn_more', $languageCode)) ?><span>↗</span></a>
  </div>
  <?php if ($projects !== []): ?>
    <div class="project-grid">
      <?php foreach ($projects as $project): ?>
        <article class="project-card reveal">
          <p class="kicker"><?= welga_escape((string)($project['programme'] ?? 'EU')) ?></p>
          <h3><?= welga_escape((string)$project['title']) ?></h3>
          <?php if (!empty($project['summary'])): ?><p><?= welga_escape((string)$project['summary']) ?></p><?php endif; ?>
          <span><?= welga_escape((string)($project['lifecycle_status'] ?? '')) ?></span>
        </article>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="projects-placeholder reveal"><p>WELGA / EU</p><a class="button button--outline" href="<?= welga_escape(welga_url('tekushti-evroproekti', $languageCode)) ?>"><?= welga_escape(welga_t('eu_projects', $languageCode)) ?><span>↗</span></a></div>
  <?php endif; ?>
</section>
