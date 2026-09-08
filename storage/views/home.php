<?php
declare(strict_types=1);
?>
<section class="shell hero">
  <p class="eyebrow">WELGA · furniture manufacturer</p>
  <h1>Furniture catalogue foundation</h1>
  <p>Database-driven WELGA catalogue with multilingual content, structured filters, materials and technical documents.</p>
</section>
<section class="shell section">
  <div class="section-head"><h2>Нови модели</h2><span><?= count($products ?? []) ?></span></div>
  <div class="product-grid">
    <?php foreach (($products ?? []) as $product): ?>
      <article class="product-card">
        <div class="product-media"><?php if (!empty($product['image_path'])): ?><img src="/<?= welga_escape(ltrim((string)$product['image_path'], '/')) ?>" alt="<?= welga_escape($product['name']) ?>"><?php endif; ?></div>
        <p class="model"><?= welga_escape($product['model']) ?></p>
        <h3><?= welga_escape($product['name']) ?></h3>
        <?php if (!empty($product['short_description'])): ?><p><?= welga_escape($product['short_description']) ?></p><?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>
</section>
