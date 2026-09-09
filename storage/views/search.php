<?php
declare(strict_types=1);

$languageCode = (string)($language['code'] ?? 'bg');
$query = (string)($query ?? '');
$products = $products ?? [];
?>
<section class="search-page shell-wide">
  <div class="search-page__head reveal">
    <p class="kicker">WELGA / <?= welga_escape(welga_t('search', $languageCode)) ?></p>
    <form class="search-page__form" action="<?= welga_escape(welga_url('search', $languageCode)) ?>" method="get">
      <input name="q" type="search" value="<?= welga_escape($query) ?>" placeholder="<?= welga_escape(welga_t('search_placeholder', $languageCode)) ?>" autofocus>
      <button type="submit">↗</button>
    </form>
    <?php if ($query !== ''): ?><p class="search-page__count"><strong><?= count($products) ?></strong> <?= welga_escape(welga_t('model', $languageCode)) ?></p><?php endif; ?>
  </div>

  <?php if ($products !== []): ?>
    <div class="product-grid product-grid--search">
      <?php foreach ($products as $cardProduct): ?><div class="reveal"><?php require WELGA_STORAGE . '/views/partials/product-card.php'; ?></div><?php endforeach; ?>
    </div>
  <?php elseif ($query !== ''): ?>
    <p class="empty-state"><?= welga_escape(welga_t('no_products', $languageCode)) ?></p>
  <?php endif; ?>
</section>
