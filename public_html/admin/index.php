<?php
declare(strict_types=1);

require __DIR__ . '/../../storage/bootstrap.php';
header('X-Robots-Tag: noindex, nofollow, noarchive', true);
header('Cache-Control: no-store, no-cache, must-revalidate');

function admin_redirect(): never { welga_redirect('/admin/'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!welga_verify_csrf($_POST['csrf'] ?? null)) {
        http_response_code(400);
        exit('Невалидна защитна сесия.');
    }

    $action = (string)($_POST['action'] ?? '');
    if ($action === 'logout' && welga_admin_authenticated()) {
        welga_admin_logout();
        admin_redirect();
    }

    if ($action === 'login' && !welga_admin_authenticated()) {
        $locked = welga_admin_login_locked();
        if ($locked > 0) {
            $error = 'Входът е временно заключен.';
        } elseif (welga_admin_login(trim((string)($_POST['username'] ?? '')), (string)($_POST['password'] ?? ''))) {
            welga_admin_clear_login_attempts();
            admin_redirect();
        } else {
            welga_admin_register_failed_login();
            $error = 'Невалидно потребителско име или парола.';
        }
    }
}

if (!welga_admin_is_configured()):
?><!doctype html><html lang="bg"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>WELGA Admin</title><link rel="stylesheet" href="/admin/admin.css"></head><body class="login-page"><main class="login-card"><div class="admin-logo">WELGA</div><h1>Администрацията не е активирана</h1><p>Създайте защитения <code>storage/data/admin.json</code> при deployment. Публична setup форма умишлено не е включена.</p></main></body></html><?php exit; endif;

if (!welga_admin_authenticated()):
?><!doctype html><html lang="bg"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Вход | WELGA Admin</title><link rel="stylesheet" href="/admin/admin.css"></head><body class="login-page"><main class="login-card"><div class="admin-logo">WELGA</div><h1>Администрация</h1><p>Каталог, тапицерии и съдържание.</p><?php if ($error !== ''): ?><div class="notice error"><?= welga_escape($error) ?></div><?php endif; ?><form method="post"><input type="hidden" name="action" value="login"><input type="hidden" name="csrf" value="<?= welga_escape(welga_csrf_token()) ?>"><label>Потребител<input type="text" name="username" value="admin" autocomplete="username" required></label><label>Парола<input type="password" name="password" autocomplete="current-password" required></label><button class="btn primary" type="submit">Вход</button></form></main></body></html><?php exit; endif;

$counts = [];
$dbOk = welga_db_available();
if ($dbOk) {
    foreach ([
        'Продукти' => 'catalog_products',
        'Категории' => 'catalog_categories',
        'Тапицерии' => 'material_collections',
        'Цветове' => 'material_colors',
        'Услуги' => 'cms_services',
        'Европроекти' => 'cms_projects',
    ] as $label => $table) {
        $row = welga_db_one('SELECT COUNT(*) AS total FROM ' . $table);
        $counts[$label] = (int)($row['total'] ?? 0);
    }
}
$modules = ['Каталог', 'Тапицерии', 'Услуги', 'Европроекти', 'Страници', 'Медия', 'Настройки'];
?><!doctype html><html lang="bg"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>WELGA Admin</title><link rel="stylesheet" href="/admin/admin.css"></head><body><div class="admin-shell"><aside class="sidebar"><div class="admin-logo">WELGA</div><nav><?php foreach ($modules as $module): ?><a href="#"><?= welga_escape($module) ?></a><?php endforeach; ?></nav><form method="post"><input type="hidden" name="action" value="logout"><input type="hidden" name="csrf" value="<?= welga_escape(welga_csrf_token()) ?>"><button type="submit" class="logout">Изход</button></form></aside><main class="dashboard"><header><div><p class="eyebrow">ETKO Catalog CMS</p><h1>WELGA Admin</h1></div><span class="db-status <?= $dbOk ? 'ok' : 'off' ?>"><?= $dbOk ? 'Базата е свързана' : 'Базата не е конфигурирана' ?></span></header><section class="cards"><?php foreach ($counts ?: array_fill_keys(['Продукти','Категории','Тапицерии','Цветове','Услуги','Европроекти'], 0) as $label => $total): ?><article><strong><?= number_format((int)$total, 0, '.', ' ') ?></strong><span><?= welga_escape($label) ?></span></article><?php endforeach; ?></section><section class="panel"><h2>Foundation status</h2><p>Администрацията вече използва ETKO security foundation и е подготвена за MySQL модулите на WELGA. Следва CRUD слой за каталог и тапицерии.</p></section></main></div></body></html>
