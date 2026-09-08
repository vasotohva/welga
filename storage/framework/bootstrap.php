<?php
declare(strict_types=1);

if (!defined('WELGA_ROOT')) {
    define('WELGA_ROOT', dirname(__DIR__, 2));
    define('WELGA_PUBLIC', WELGA_ROOT . '/public_html');
    define('WELGA_STORAGE', WELGA_ROOT . '/storage');
}

require_once WELGA_STORAGE . '/framework/config.php';

$app = welga_config('app');
date_default_timezone_set((string)($app['timezone'] ?? 'Europe/Sofia'));

if (session_status() !== PHP_SESSION_ACTIVE) {
    $sessionPath = WELGA_STORAGE . '/sessions';
    if (!is_dir($sessionPath)) {
        @mkdir($sessionPath, 0750, true);
    }

    session_name((string)($app['session_name'] ?? 'WELGASESSID'));
    session_save_path($sessionPath);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once WELGA_STORAGE . '/framework/functions.php';
require_once WELGA_STORAGE . '/framework/database.php';
require_once WELGA_STORAGE . '/framework/localization.php';
require_once WELGA_STORAGE . '/framework/ui.php';
require_once WELGA_STORAGE . '/framework/router.php';
require_once WELGA_STORAGE . '/framework/catalog.php';
require_once WELGA_STORAGE . '/framework/catalog_ui.php';
require_once WELGA_STORAGE . '/framework/content.php';
require_once WELGA_STORAGE . '/framework/admin_auth.php';

welga_csrf_token();
