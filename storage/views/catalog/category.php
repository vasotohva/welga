<?php
declare(strict_types=1);
?>
<section class="shell section">
  <p class="eyebrow">Категория</p>
  <h1><?= welga_escape($category['name'] ?? '') ?></h1>
  <?php if (!empty($category['description'])): ?><div class="rich-text"><?= $category['description'] ?></div><?php endif; ?>
</section>
