<?php
declare(strict_types=1);

$cardProduct = $cardProduct ?? [];
$languageCode = (string)($language['code'] ?? 'bg');
$routePath = trim((string)($cardProduct['route_path'] ?? ''), '/');
$href = $routePath !== '' ? welga_url($routePath, $languageCode) : '#';
$imagePath = trim((string)($cardProduct['image_path'] ?? ''), '/');
$filterIds = (string)($cardProduct['filter_ids'] ?? '');
$published = (string)($cardProduct['published_at'] ?? $cardProduct['created_at'] ?? '');
$nameForSort = (string)($cardProduct['name'] ?? '');
$nameForSort = function_exists('mb_strtolower') ? mb_strtolower($nameForSort, 'UTF-8') : strtolower($nameForSort);
?>
<article class="product-card" data-product-card data-filter-ids=",<?= welga_escape($filterIds) ?>," data-name="<?= welga_escape($nameForSort) ?>" data-published="<?= welga_escape($published) ?>">
  <a class="product-card__media" href="<?= welga_escape($href) ?>" aria-label="<?= welga_escape((string)($cardProduct['name'] ?? '')) ?>">
    <?php if ($imagePath !== ''): ?>
      <img src="/<?= welga_escape($imagePath) ?>" alt="<?= welga_escape((string)($cardProduct['name'] ?? '')) ?>" loading="lazy" decoding="async">
    <?php else: ?>
      <span class="media-placeholder" aria-hidden="true"></span>
    <?php endif; ?>
    <span class="product-card__arrow" aria-hidden="true">↗</span>
  </a>
  <div class="product-card__body">
    <p class="product-card__model"><?= welga_escape((string)($cardProduct['model'] ?? '')) ?></p>
    <h3 class="product-card__title"><a href="<?= welga_escape($href) ?>"><?= welga_escape((string)($cardProduct['name'] ?? '')) ?></a></h3>
    <?php if (!empty($cardProduct['short_description'])): ?>
      <p class="product-card__summary"><?= welga_escape((string)$cardProduct['short_description']) ?></p>
    <?php endif; ?>
  </div>
</article>
