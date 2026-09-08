<?php
declare(strict_types=1);
?>
<article class="shell section product-page">
  <p class="eyebrow"><?= welga_escape($product['model'] ?? '') ?></p>
  <h1><?= welga_escape($product['name'] ?? '') ?></h1>
  <?php if (!empty($product['short_description'])): ?><p class="lead"><?= welga_escape($product['short_description']) ?></p><?php endif; ?>
  <?php if (!empty($product['image_path'])): ?><img class="product-hero-image" src="/<?= welga_escape(ltrim((string)$product['image_path'], '/')) ?>" alt="<?= welga_escape($product['name'] ?? '') ?>"><?php endif; ?>
  <div class="rich-text"><?= $product['description'] ?? '' ?></div>
</article>
