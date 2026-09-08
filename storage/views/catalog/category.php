<?php
declare(strict_types=1);

$languageCode = (string)($language['code'] ?? 'bg');
$products = $products ?? [];
$filterGroups = $filterGroups ?? [];
?>

<section class="category-hero shell-wide reveal">
  <div class="category-hero__copy">
    <p class="kicker"><?= welga_escape(welga_t('category', $languageCode)) ?></p>
    <h1><?= welga_escape((string)($category['name'] ?? '')) ?></h1>
    <?php if (!empty($category['description'])): ?><div class="category-description rich-text"><?= $category['description'] ?></div><?php endif; ?>
  </div>
  <div class="category-hero__count"><strong data-total-products><?= count($products) ?></strong><span><?= welga_escape(welga_t('model', $languageCode)) ?></span></div>
</section>

<div class="mobile-filter-bar" data-mobile-filter-bar>
  <button type="button" data-filter-toggle><span><?= welga_escape(welga_t('filter', $languageCode)) ?></span><b data-active-filter-count>0</b></button>
  <span data-visible-count><?= count($products) ?></span>
</div>

<section class="category-catalog shell-wide" data-category-catalog>
  <aside class="filter-panel" data-filter-panel aria-label="<?= welga_escape(welga_t('filter', $languageCode)) ?>">
    <div class="filter-panel__top">
      <div><p class="kicker">WELGA / <?= welga_escape(welga_t('filter', $languageCode)) ?></p><h2><?= welga_escape(welga_t('filter', $languageCode)) ?></h2></div>
      <button class="filter-close mobile-only" type="button" data-filter-close aria-label="<?= welga_escape(welga_t('close', $languageCode)) ?>">×</button>
    </div>

    <div class="filter-sort mobile-only">
      <label for="category-sort-mobile"><?= welga_escape(welga_t('sort', $languageCode)) ?></label>
      <select id="category-sort-mobile" data-sort-select>
        <option value="latest"><?= welga_escape(welga_t('latest_first', $languageCode)) ?></option>
        <option value="name"><?= welga_escape(welga_t('name_az', $languageCode)) ?></option>
      </select>
    </div>

    <div class="filter-groups">
      <?php foreach ($filterGroups as $group): ?>
        <details class="filter-group" open>
          <summary><span><?= welga_escape((string)$group['name']) ?></span><i>＋</i></summary>
          <div class="filter-group__options">
            <?php foreach ($group['filters'] as $filter): ?>
              <label class="filter-option">
                <input type="checkbox" value="<?= (int)$filter['filter_id'] ?>" data-filter-input data-filter-group="<?= (int)$group['filter_group_id'] ?>">
                <span><?= welga_escape((string)$filter['name']) ?></span>
                <small><?= (int)$filter['product_count'] ?></small>
              </label>
            <?php endforeach; ?>
          </div>
        </details>
      <?php endforeach; ?>
    </div>

    <div class="filter-panel__actions">
      <button class="button button--dark filter-apply" type="button" data-filter-apply><?= welga_escape(welga_t('show_models', $languageCode)) ?> <span data-filter-result-count><?= count($products) ?></span></button>
      <button class="text-button" type="button" data-filter-clear><?= welga_escape(welga_t('clear_filters', $languageCode)) ?></button>
    </div>
  </aside>

  <div class="category-results">
    <div class="category-toolbar">
      <button class="filter-trigger" type="button" data-filter-toggle><span><?= welga_escape(welga_t('filter', $languageCode)) ?></span><b data-active-filter-count>0</b></button>
      <div class="category-toolbar__count"><span data-visible-count><?= count($products) ?></span> / <?= count($products) ?></div>
      <label class="sort-control desktop-only"><span><?= welga_escape(welga_t('sort', $languageCode)) ?></span><select data-sort-select><option value="latest"><?= welga_escape(welga_t('latest_first', $languageCode)) ?></option><option value="name"><?= welga_escape(welga_t('name_az', $languageCode)) ?></option></select></label>
    </div>

    <div class="product-grid product-grid--category" data-category-products>
      <?php foreach ($products as $cardProduct): ?>
        <div class="product-card-wrap reveal" data-product-wrap><?php require WELGA_STORAGE . '/views/partials/product-card.php'; ?></div>
      <?php endforeach; ?>
    </div>
    <p class="empty-state category-empty" data-category-empty hidden><?= welga_escape(welga_t('no_products', $languageCode)) ?></p>
  </div>
</section>

<button class="filter-scrim" type="button" data-filter-close hidden aria-label="<?= welga_escape(welga_t('close', $languageCode)) ?>"></button>
