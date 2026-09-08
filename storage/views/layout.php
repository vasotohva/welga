<?php
declare(strict_types=1);

$site = welga_config('site');
$language = $language ?? welga_default_language();
$title = $title ?? (string)($site['name'] ?? 'WELGA');
$description = $description ?? '';
?>
<!doctype html>
<html lang="<?= welga_escape((string)$language['code']) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= welga_escape($title) ?></title>
  <?php if ($description !== ''): ?><meta name="description" content="<?= welga_escape($description) ?>"><?php endif; ?>
  <meta name="theme-color" content="#ffffff">
  <link rel="stylesheet" href="/assets/css/site.css?v=0.1.0">
</head>
<body>
  <header class="site-header">
    <a class="brand" href="<?= welga_escape(welga_url('', (string)$language['code'])) ?>">WELGA</a>
    <nav aria-label="Main navigation">
      <a href="<?= welga_escape(welga_url('', (string)$language['code'])) ?>">Начало</a>
      <a href="<?= welga_escape(welga_url('search', (string)$language['code'])) ?>">Търсене</a>
    </nav>
    <div class="languages"><?php foreach (welga_languages() as $item): ?><a href="<?= welga_escape(welga_url('', (string)$item['code'])) ?>"><?= welga_escape(strtoupper((string)$item['code'])) ?></a><?php endforeach; ?></div>
  </header>
  <main id="main-content"><?php require $viewFile; ?></main>
  <footer class="site-footer"><span>© <?= date('Y') ?> WELGA</span></footer>
  <script src="/assets/js/site.js?v=0.1.0" defer></script>
</body>
</html>
