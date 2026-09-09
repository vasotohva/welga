<?php
declare(strict_types=1);

$site = welga_config('site');
$language = $language ?? welga_default_language();
$languageCode = (string)($language['code'] ?? 'bg');
$languageId = (int)($language['language_id'] ?? 1);
$title = $title ?? (string)($site['name'] ?? 'WELGA');
$description = $description ?? '';
$bodyClass = trim((string)($bodyClass ?? ''));
$navigationCategories = welga_catalog_categories($languageId);
$categoryTree = welga_catalog_category_tree($navigationCategories);

$categoryHref = static function (array $category) use ($languageCode): string {
    $route = trim((string)($category['route_path'] ?? ''), '/');
    return $route !== '' ? welga_url($route, $languageCode) : '#';
};
?>
<!doctype html>
<html lang="<?= welga_escape($languageCode) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= welga_escape($title) ?></title>
  <?php if ($description !== ''): ?><meta name="description" content="<?= welga_escape($description) ?>"><?php endif; ?>
  <meta name="theme-color" content="#fbfaf8">
  <meta name="color-scheme" content="light">
  <link rel="stylesheet" href="/assets/css/site.css?v=0.3.0">
  <link rel="stylesheet" href="/assets/css/mobile-v2.css?v=0.3.0">
</head>
<body class="<?= welga_escape($bodyClass) ?>">
  <a class="skip-link" href="#main-content">Skip to content</a>

  <header class="site-header" data-site-header>
    <div class="site-header__meta">
      <span><?= welga_escape(welga_t('made_in_bulgaria', $languageCode)) ?></span>
      <div class="language-switcher" aria-label="<?= welga_escape(welga_t('language', $languageCode)) ?>">
        <?php foreach (welga_languages() as $item): ?>
          <a class="<?= ($item['code'] ?? '') === $languageCode ? 'is-active' : '' ?>" href="<?= welga_escape(welga_url('', (string)$item['code'])) ?>" hreflang="<?= welga_escape((string)$item['code']) ?>"><?= welga_escape(strtoupper((string)$item['code'])) ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="site-header__main">
      <a class="brand" href="<?= welga_escape(welga_url('', $languageCode)) ?>" aria-label="WELGA home"><img src="/assets/img/logo-welga-full-grey.svg" alt="WELGA" width="156" height="28"></a>

      <nav class="desktop-nav" aria-label="Main navigation">
        <button class="nav-link nav-link--button" type="button" data-mega-toggle aria-expanded="false" aria-controls="products-mega">
          <?= welga_escape(welga_t('products', $languageCode)) ?><span aria-hidden="true">＋</span>
        </button>
        <a class="nav-link" href="<?= welga_escape(welga_url('tapicerii', $languageCode)) ?>"><?= welga_escape(welga_t('materials', $languageCode)) ?></a>
        <a class="nav-link" href="<?= welga_escape(welga_url('welga-uslugi', $languageCode)) ?>"><?= welga_escape(welga_t('services', $languageCode)) ?></a>
        <a class="nav-link" href="<?= welga_escape(welga_url('galeria', $languageCode)) ?>"><?= welga_escape(welga_t('gallery', $languageCode)) ?></a>
        <a class="nav-link" href="<?= welga_escape(welga_url('za-nas', $languageCode)) ?>"><?= welga_escape(welga_t('about', $languageCode)) ?></a>
      </nav>

      <div class="site-header__actions">
        <button class="icon-button search-toggle" type="button" data-search-toggle aria-label="<?= welga_escape(welga_t('search', $languageCode)) ?>" aria-expanded="false" aria-controls="site-search">
          <?= welga_icon('search') ?>
          <span class="desktop-only"><?= welga_escape(welga_t('search', $languageCode)) ?></span>
        </button>
        <a class="contact-link desktop-only" href="<?= welga_escape(welga_url('kontakti', $languageCode)) ?>"><?= welga_escape(welga_t('contact', $languageCode)) ?></a>
        <button class="icon-button menu-toggle mobile-only" type="button" data-menu-toggle aria-label="<?= welga_escape(welga_t('menu', $languageCode)) ?>" aria-expanded="false" aria-controls="mobile-menu">
          <?= welga_icon('menu') ?>
        </button>
      </div>
    </div>

    <div class="mega-menu" id="products-mega" data-mega-menu hidden>
      <div class="mega-menu__inner shell-wide">
        <div class="mega-menu__intro">
          <p class="kicker">WELGA / <?= welga_escape(welga_t('products', $languageCode)) ?></p>
          <p><?= welga_escape(welga_t('manufacturer', $languageCode)) ?></p>
        </div>
        <div class="mega-menu__categories">
          <?php foreach ($categoryTree as $root): ?>
            <?php $rootChildren = $root['children'] ?? []; ?>
            <?php if ($rootChildren === []): ?>
              <a class="mega-category mega-category--root" href="<?= welga_escape($categoryHref($root)) ?>"><?= welga_escape((string)$root['name']) ?><?= welga_icon('arrow-up-right') ?></a>
            <?php else: ?>
              <?php foreach ($rootChildren as $category): ?>
                <div class="mega-category-group">
                  <a class="mega-category" href="<?= welga_escape($categoryHref($category)) ?>"><?= welga_escape((string)$category['name']) ?><?= welga_icon('arrow-up-right') ?></a>
                  <?php if (!empty($category['children'])): ?>
                    <div class="mega-subcategories">
                      <?php foreach ($category['children'] as $child): ?>
                        <a href="<?= welga_escape($categoryHref($child)) ?>"><?= welga_escape((string)$child['name']) ?></a>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="site-search" id="site-search" data-site-search hidden>
      <form class="site-search__form shell-wide" action="<?= welga_escape(welga_url('search', $languageCode)) ?>" method="get" role="search">
        <label class="sr-only" for="global-search"><?= welga_escape(welga_t('search', $languageCode)) ?></label>
        <input id="global-search" name="q" type="search" autocomplete="off" placeholder="<?= welga_escape(welga_t('search_placeholder', $languageCode)) ?>">
        <button type="submit" aria-label="<?= welga_escape(welga_t('search', $languageCode)) ?>"><?= welga_icon('arrow-right') ?></button>
      </form>
    </div>
  </header>

  <aside class="mobile-menu" id="mobile-menu" data-mobile-menu aria-hidden="true">
    <div class="mobile-menu__top">
      <a class="brand" href="<?= welga_escape(welga_url('', $languageCode)) ?>"><img src="/assets/img/logo-welga-full-grey.svg" alt="WELGA" width="150" height="26"></a>
      <button class="icon-button" type="button" data-menu-close aria-label="<?= welga_escape(welga_t('close', $languageCode)) ?>"><?= welga_icon('close') ?></button>
    </div>
    <nav class="mobile-menu__nav" aria-label="Mobile navigation">
      <details open>
        <summary><?= welga_escape(welga_t('products', $languageCode)) ?><span>＋</span></summary>
        <div class="mobile-menu__categories">
          <?php foreach ($categoryTree as $root): ?>
            <?php foreach (($root['children'] ?? [$root]) as $category): ?>
              <a href="<?= welga_escape($categoryHref($category)) ?>"><?= welga_escape((string)$category['name']) ?></a>
              <?php foreach (($category['children'] ?? []) as $child): ?>
                <a class="is-child" href="<?= welga_escape($categoryHref($child)) ?>"><?= welga_escape((string)$child['name']) ?></a>
              <?php endforeach; ?>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </div>
      </details>
      <a href="<?= welga_escape(welga_url('tapicerii', $languageCode)) ?>"><?= welga_escape(welga_t('materials', $languageCode)) ?></a>
      <a href="<?= welga_escape(welga_url('welga-uslugi', $languageCode)) ?>"><?= welga_escape(welga_t('services', $languageCode)) ?></a>
      <a href="<?= welga_escape(welga_url('galeria', $languageCode)) ?>"><?= welga_escape(welga_t('gallery', $languageCode)) ?></a>
      <a href="<?= welga_escape(welga_url('za-nas', $languageCode)) ?>"><?= welga_escape(welga_t('about', $languageCode)) ?></a>
      <a href="<?= welga_escape(welga_url('kontakti', $languageCode)) ?>"><?= welga_escape(welga_t('contact', $languageCode)) ?></a>
    </nav>
    <div class="mobile-menu__languages">
      <?php foreach (welga_languages() as $item): ?><a class="<?= ($item['code'] ?? '') === $languageCode ? 'is-active' : '' ?>" href="<?= welga_escape(welga_url('', (string)$item['code'])) ?>"><?= welga_escape(strtoupper((string)$item['code'])) ?></a><?php endforeach; ?>
    </div>
  </aside>
  <button class="page-scrim" data-page-scrim type="button" aria-label="<?= welga_escape(welga_t('close', $languageCode)) ?>" hidden></button>

  <main id="main-content"><?php require $viewFile; ?></main>

  <footer class="site-footer">
    <div class="shell-wide site-footer__grid">
      <div class="site-footer__brand">
        <a class="brand brand--footer" href="<?= welga_escape(welga_url('', $languageCode)) ?>"><img src="/assets/img/logo-welga-full-grey.svg" alt="WELGA" width="180" height="32"></a>
        <p><?= welga_escape(welga_t('manufacturer', $languageCode)) ?></p>
        <p class="footer-note"><?= welga_escape(welga_t('made_in_bulgaria', $languageCode)) ?></p>
      </div>
      <div class="site-footer__column">
        <p class="footer-heading"><?= welga_escape(welga_t('products', $languageCode)) ?></p>
        <?php foreach (array_slice($navigationCategories, 0, 6) as $category): ?>
          <a href="<?= welga_escape($categoryHref($category)) ?>"><?= welga_escape((string)$category['name']) ?></a>
        <?php endforeach; ?>
      </div>
      <div class="site-footer__column">
        <p class="footer-heading">WELGA</p>
        <a href="<?= welga_escape(welga_url('tapicerii', $languageCode)) ?>"><?= welga_escape(welga_t('materials', $languageCode)) ?></a>
        <a href="<?= welga_escape(welga_url('welga-uslugi', $languageCode)) ?>"><?= welga_escape(welga_t('services', $languageCode)) ?></a>
        <a href="<?= welga_escape(welga_url('galeria', $languageCode)) ?>"><?= welga_escape(welga_t('gallery', $languageCode)) ?></a>
        <a href="<?= welga_escape(welga_url('kontakti', $languageCode)) ?>"><?= welga_escape(welga_t('contact', $languageCode)) ?></a>
      </div>
      <div class="site-footer__column site-footer__language">
        <p class="footer-heading"><?= welga_escape(welga_t('language', $languageCode)) ?></p>
        <?php foreach (welga_languages() as $item): ?><a class="<?= ($item['code'] ?? '') === $languageCode ? 'is-active' : '' ?>" href="<?= welga_escape(welga_url('', (string)$item['code'])) ?>"><?= welga_escape((string)$item['native_name']) ?></a><?php endforeach; ?>
      </div>
    </div>
    <div class="shell-wide site-footer__bottom">
      <span>© <?= date('Y') ?> WELGA</span>
      <span><?= welga_escape(welga_t('european_production', $languageCode)) ?></span>
    </div>
  </footer>

  <script src="/assets/js/site.js?v=0.3.0" defer></script>
  <script src="/assets/js/product-configurations.js?v=0.1.0" defer></script>
</body>
</html>
