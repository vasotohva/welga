<?php
declare(strict_types=1);

$languageCode = (string)($language['code'] ?? 'bg');
$gallery = $gallery ?? [];
$documents = $documents ?? [];
$features = $features ?? [];
$attributes = $attributes ?? [];
$options = $options ?? [];
$upholstery = $upholstery ?? ['groups' => [], 'collections' => []];
$relatedProducts = $relatedProducts ?? [];

$primaryImage = trim((string)($product['image_path'] ?? ''), '/');
$images = [];
if ($primaryImage !== '') {
    $images[] = ['path' => $primaryImage, 'alt_text' => (string)($product['name'] ?? '')];
}
foreach ($gallery as $image) {
    $path = trim((string)($image['path'] ?? ''), '/');
    if ($path !== '' && !in_array($path, array_column($images, 'path'), true)) {
        $images[] = ['path' => $path, 'alt_text' => (string)($image['alt_text'] ?? $product['name'] ?? '')];
    }
}

$technicalDocuments = [];
$priceDocuments = [];
foreach ($documents as $document) {
    $role = strtolower((string)($document['role'] ?? $document['document_type'] ?? ''));
    if (str_contains($role, 'price')) {
        $priceDocuments[] = $document;
    } else {
        $technicalDocuments[] = $document;
    }
}

$attributeValue = static function (array $attribute): string {
    if (!empty($attribute['selected_value'])) {
        return (string)$attribute['selected_value'];
    }
    if ($attribute['value_boolean'] !== null) {
        return (bool)$attribute['value_boolean'] ? 'Да' : 'Не';
    }
    if ($attribute['value_number'] !== null) {
        $value = rtrim(rtrim((string)$attribute['value_number'], '0'), '.');
        return $value . (!empty($attribute['unit']) ? ' ' . $attribute['unit'] : '');
    }
    return trim((string)($attribute['value_text'] ?? ''));
};

$materialGroups = [];
foreach (($upholstery['collections'] ?? []) as $collection) {
    $materialGroups[(string)$collection['material_type_name']][] = $collection;
}
$customerSupplied = false;
foreach (($upholstery['groups'] ?? []) as $group) {
    if (($group['mode'] ?? '') === 'customer_supplied' || ($group['code'] ?? '') === 'F') {
        $customerSupplied = true;
        break;
    }
}
?>

<article class="product-page">
  <section class="product-intro shell-wide" id="model">
    <div class="product-intro__gallery reveal">
      <?php if ($images !== []): ?>
        <button class="product-main-image" type="button" data-gallery-open="0" aria-label="<?= welga_escape(welga_t('product_gallery', $languageCode)) ?>">
          <img src="/<?= welga_escape($images[0]['path']) ?>" alt="<?= welga_escape($images[0]['alt_text']) ?>" fetchpriority="high">
          <span>01 / <?= str_pad((string)count($images), 2, '0', STR_PAD_LEFT) ?></span>
        </button>
        <?php if (count($images) > 1): ?>
          <div class="product-intro__thumbs">
            <?php foreach (array_slice($images, 1, 4) as $index => $image): ?>
              <button type="button" data-gallery-open="<?= $index + 1 ?>"><img src="/<?= welga_escape($image['path']) ?>" alt="<?= welga_escape($image['alt_text']) ?>" loading="lazy"></button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="product-main-image"><span class="media-placeholder media-placeholder--hero"></span></div>
      <?php endif; ?>
    </div>

    <div class="product-intro__info reveal">
      <div class="product-info-sticky">
        <p class="kicker"><?= welga_escape(welga_t('model', $languageCode)) ?> / <?= welga_escape((string)($product['model'] ?? '')) ?></p>
        <h1><?= welga_escape((string)($product['name'] ?? '')) ?></h1>
        <?php if (!empty($product['short_description'])): ?><p class="product-lead"><?= welga_escape((string)$product['short_description']) ?></p><?php endif; ?>

        <?php if ($features !== []): ?>
          <ul class="product-feature-pills" aria-label="Product features">
            <?php foreach (array_slice($features, 0, 5) as $feature): ?><li><?= welga_escape((string)$feature['name']) ?></li><?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <div class="product-actions">
          <button class="button button--dark button--wide" type="button" data-dialog-open="inquiry-dialog"><?= welga_escape(welga_t('inquiry', $languageCode)) ?><span>↗</span></button>
          <div class="product-document-actions">
            <?php if ($technicalDocuments !== []): ?>
              <button class="text-link" type="button" data-pdf-open="/<?= welga_escape(ltrim((string)$technicalDocuments[0]['path'], '/')) ?>" data-pdf-title="<?= welga_escape((string)($technicalDocuments[0]['title'] ?? welga_t('technical_info', $languageCode))) ?>"><?= welga_escape(welga_t('technical_info', $languageCode)) ?><span>↓</span></button>
            <?php endif; ?>
            <?php if ($priceDocuments !== []): ?>
              <button class="text-link" type="button" data-pdf-open="/<?= welga_escape(ltrim((string)$priceDocuments[0]['path'], '/')) ?>" data-pdf-title="<?= welga_escape((string)($priceDocuments[0]['title'] ?? welga_t('price_list', $languageCode))) ?>"><?= welga_escape(welga_t('price_list', $languageCode)) ?><span>↓</span></button>
            <?php endif; ?>
          </div>
        </div>

        <nav class="product-section-nav" aria-label="Product sections">
          <a href="#model"><span>01</span><?= welga_escape(welga_t('model', $languageCode)) ?></a>
          <a href="#configurations"><span>02</span><?= welga_escape(welga_t('configurations', $languageCode)) ?></a>
          <a href="#upholstery"><span>03</span><?= welga_escape(welga_t('upholstery', $languageCode)) ?></a>
          <a href="#details"><span>04</span><?= welga_escape(welga_t('details', $languageCode)) ?></a>
        </nav>
      </div>
    </div>
  </section>

  <section class="product-section shell-wide" id="configurations">
    <div class="product-section__heading reveal"><p class="section-number">02</p><h2><?= welga_escape(welga_t('configurations', $languageCode)) ?></h2></div>
    <div class="product-section__body product-config-grid">
      <?php if ($options !== []): ?>
        <div class="option-groups reveal">
          <?php foreach ($options as $option): ?>
            <section class="option-group">
              <div class="option-group__head"><h3><?= welga_escape((string)$option['name']) ?></h3><span><?= count($option['values']) ?></span></div>
              <div class="option-values <?= ($option['input_type'] ?? '') === 'swatch' ? 'option-values--swatches' : '' ?>">
                <?php foreach ($option['values'] as $value): ?>
                  <button class="option-value" type="button" data-option-value="<?= welga_escape((string)$value['code']) ?>">
                    <?php if (!empty($value['image_path'])): ?><img src="/<?= welga_escape(ltrim((string)$value['image_path'], '/')) ?>" alt="" loading="lazy"><?php endif; ?>
                    <span><?= welga_escape((string)$value['name']) ?></span>
                  </button>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="attribute-table reveal">
        <?php foreach ($attributes as $attribute): ?>
          <?php $value = $attributeValue($attribute); if ($value === '') continue; ?>
          <div class="attribute-row"><span><?= welga_escape((string)$attribute['name']) ?></span><b><?= welga_escape($value) ?></b></div>
        <?php endforeach; ?>
        <?php if ($attributes === [] && $technicalDocuments !== []): ?>
          <p class="muted-copy"><?= welga_escape(welga_t('technical_info', $languageCode)) ?></p>
        <?php endif; ?>
        <?php if ($technicalDocuments !== []): ?>
          <button class="button button--outline" type="button" data-pdf-open="/<?= welga_escape(ltrim((string)$technicalDocuments[0]['path'], '/')) ?>" data-pdf-title="<?= welga_escape((string)($technicalDocuments[0]['title'] ?? welga_t('technical_info', $languageCode))) ?>"><?= welga_escape(welga_t('technical_info', $languageCode)) ?><span>↗</span></button>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="product-section product-section--soft" id="upholstery">
    <div class="shell-wide">
      <div class="product-section__heading reveal"><p class="section-number">03</p><h2><?= welga_escape(welga_t('upholstery', $languageCode)) ?></h2></div>
      <div class="upholstery-groups">
        <?php foreach ($materialGroups as $materialType => $collections): ?>
          <section class="upholstery-type reveal">
            <div class="upholstery-type__head"><h3><?= welga_escape($materialType) ?></h3><span><?= array_sum(array_map(static fn(array $c): int => (int)$c['color_count'], $collections)) ?> colors</span></div>
            <div class="upholstery-collections">
              <?php foreach ($collections as $collection): ?>
                <button class="upholstery-card" type="button" data-upholstery-collection="<?= (int)$collection['collection_id'] ?>">
                  <span class="upholstery-card__preview"><?php if (!empty($collection['preview_path'])): ?><img src="/<?= welga_escape(ltrim((string)$collection['preview_path'], '/')) ?>" alt="<?= welga_escape((string)$collection['name']) ?>" loading="lazy"><?php else: ?><span class="material-placeholder"></span><?php endif; ?></span>
                  <span class="upholstery-card__meta"><b><?= welga_escape((string)$collection['name']) ?></b><small><?= welga_escape((string)$collection['price_group_code']) ?> · <?= (int)$collection['color_count'] ?> colors</small></span>
                </button>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endforeach; ?>
        <?php if ($customerSupplied): ?>
          <section class="customer-upholstery reveal"><span class="kicker">F</span><div><h3><?= welga_escape(welga_t('customer_upholstery', $languageCode)) ?></h3><p><?= $languageCode === 'bg' ? 'Моделът допуска изпълнение с предоставена от клиента тапицерия. Тази опция е отделен ценови режим и няма цветови мостри.' : ($languageCode === 'de' ? 'Das Modell kann mit einem vom Kunden bereitgestellten Bezugsstoff gefertigt werden. Diese Option ist ein separater Preismodus ohne Farbmuster.' : 'This model can be produced with customer-supplied upholstery. It is a separate pricing mode and does not generate colour swatches.') ?></p></div></section>
        <?php endif; ?>
        <?php if ($materialGroups === [] && !$customerSupplied): ?><p class="empty-state"><?= welga_escape(welga_t('available_materials', $languageCode)) ?> —</p><?php endif; ?>
      </div>
    </div>
  </section>

  <section class="product-section shell-wide" id="details">
    <div class="product-section__heading reveal"><p class="section-number">04</p><h2><?= welga_escape(welga_t('details', $languageCode)) ?></h2></div>
    <div class="product-details-grid">
      <div class="rich-text rich-text--product reveal"><?= $product['description'] ?? '' ?></div>
      <?php if ($features !== []): ?>
        <div class="feature-list reveal">
          <?php foreach ($features as $index => $feature): ?><div class="feature-list__row"><span><?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?></span><p><?= welga_escape((string)$feature['name']) ?></p></div><?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <?php if (count($images) > 1): ?>
    <section class="product-section product-gallery-section shell-wide" id="gallery">
      <div class="product-section__heading reveal"><p class="section-number">05</p><h2><?= welga_escape(welga_t('product_gallery', $languageCode)) ?></h2></div>
      <div class="product-gallery-grid">
        <?php foreach ($images as $index => $image): ?><button class="product-gallery-tile product-gallery-tile--<?= ($index % 4) + 1 ?> reveal" type="button" data-gallery-open="<?= $index ?>"><img src="/<?= welga_escape($image['path']) ?>" alt="<?= welga_escape($image['alt_text']) ?>" loading="lazy"><span><?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?></span></button><?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($relatedProducts !== []): ?>
    <section class="product-section related-section shell-wide" id="related">
      <div class="product-section__heading reveal"><p class="section-number">06</p><h2><?= welga_escape(welga_t('related_models', $languageCode)) ?></h2></div>
      <div class="product-grid product-grid--related">
        <?php foreach ($relatedProducts as $cardProduct): ?><div class="reveal"><?php require WELGA_STORAGE . '/views/partials/product-card.php'; ?></div><?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</article>

<dialog class="pdf-dialog" id="pdf-dialog" data-pdf-dialog>
  <div class="dialog-top"><strong data-pdf-dialog-title><?= welga_escape(welga_t('technical_info', $languageCode)) ?></strong><button type="button" data-dialog-close aria-label="<?= welga_escape(welga_t('close', $languageCode)) ?>">×</button></div>
  <iframe title="PDF" data-pdf-frame></iframe>
</dialog>

<dialog class="inquiry-dialog" id="inquiry-dialog">
  <form class="inquiry-form" method="dialog" data-inquiry-form>
    <div class="dialog-top"><div><p class="kicker"><?= welga_escape((string)($product['model'] ?? '')) ?></p><h2><?= welga_escape(welga_t('inquiry', $languageCode)) ?></h2></div><button type="button" data-dialog-close aria-label="<?= welga_escape(welga_t('close', $languageCode)) ?>">×</button></div>
    <p class="inquiry-form__note"><?= welga_escape(welga_t('inquiry_note', $languageCode)) ?></p>
    <input type="hidden" name="product_id" value="<?= (int)($product['product_id'] ?? 0) ?>">
    <input type="hidden" name="model" value="<?= welga_escape((string)($product['model'] ?? '')) ?>">
    <div class="form-grid">
      <label><span><?= welga_escape(welga_t('your_name', $languageCode)) ?></span><input name="name" type="text" required></label>
      <label><span><?= welga_escape(welga_t('email', $languageCode)) ?></span><input name="email" type="email" required></label>
      <label><span><?= welga_escape(welga_t('phone', $languageCode)) ?></span><input name="phone" type="tel"></label>
      <label class="form-grid__wide"><span><?= welga_escape(welga_t('message', $languageCode)) ?></span><textarea name="message" rows="5"></textarea></label>
    </div>
    <button class="button button--dark button--wide" value="submit"><?= welga_escape(welga_t('send_inquiry', $languageCode)) ?><span>↗</span></button>
  </form>
</dialog>

<?php if ($images !== []): ?>
  <dialog class="lightbox-dialog" id="gallery-dialog" data-gallery-dialog>
    <div class="dialog-top"><strong><?= welga_escape((string)($product['model'] ?? '')) ?> / <?= welga_escape(welga_t('product_gallery', $languageCode)) ?></strong><button type="button" data-dialog-close aria-label="<?= welga_escape(welga_t('close', $languageCode)) ?>">×</button></div>
    <div class="lightbox-stage"><button type="button" data-gallery-prev aria-label="Previous">←</button><img data-gallery-image src="/<?= welga_escape($images[0]['path']) ?>" alt="<?= welga_escape($images[0]['alt_text']) ?>"><button type="button" data-gallery-next aria-label="Next">→</button></div>
    <script type="application/json" data-gallery-data><?= json_encode($images, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
  </dialog>
<?php endif; ?>
