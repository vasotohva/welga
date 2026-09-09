<?php
declare(strict_types=1);

$cardProduct = $cardProduct ?? [];
$languageCode = (string)($language['code'] ?? 'bg');
$routePath = trim((string)($cardProduct['route_path'] ?? ''), '/');
$href = $routePath !== '' ? welga_url($routePath, $languageCode) : '#';
$imagePath = trim((string)($cardProduct['image_path'] ?? ''), '/');
$filterIds = (string)($cardProduct['filter_ids'] ?? '');
$published = (string)($cardProduct['published_at'] ?? $cardProduct['created_at'] ?? '');
$displayTitle = welga_product_display_title((string)($cardProduct['name'] ?? ''), (string)($cardProduct['model'] ?? ''));
$nameForSort = $displayTitle;
$nameForSort = function_exists('mb_strtolower') ? mb_strtolower($nameForSort, 'UTF-8') : strtolower($nameForSort);
$featureHints = array_slice((array)($cardProduct['feature_hints'] ?? []), 0, 3);
?>
<article class="product-card" data-product-card data-filter-ids=",<?= welga_escape($filterIds) ?>," data-name="<?= welga_escape($nameForSort) ?>" data-published="<?= welga_escape($published) ?>">
  <a class="product-card__media" href="<?= welga_escape($href) ?>" aria-label="<?= welga_escape($displayTitle) ?>">
    <?php if ($imagePath !== ''): ?>
      <img src="/<?= welga_escape($imagePath) ?>" alt="<?= welga_escape($displayTitle) ?>" loading="lazy" decoding="async">
    <?php else: ?>
      <span class="media-placeholder" aria-hidden="true"></span>
    <?php endif; ?>
    <span class="product-card__arrow" aria-hidden="true"><?= welga_icon('arrow-up-right') ?></span>
  </a>
  <div class="product-card__body">
    <h3 class="product-card__title"><a href="<?= welga_escape($href) ?>"><?= welga_escape($displayTitle) ?></a></h3>
    <?php if (!empty($cardProduct['short_description'])): ?>
      <p class="product-card__summary"><?= welga_escape((string)$cardProduct['short_description']) ?></p>
    <?php endif; ?>
    <?php if ($featureHints !== []): ?>
      <ul class="product-card__features">
        <?php foreach ($featureHints as $feature): ?>
          <li><?= welga_icon((string)($feature['icon'] ?? 'spark')) ?><span><?= welga_escape((string)($feature['name'] ?? '')) ?></span></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <a class="product-card__cta" href="<?= welga_escape($href) ?>"><?= welga_escape(welga_t('view_model', $languageCode)) ?><?= welga_icon('arrow-right') ?></a>
  </div>
</article>
