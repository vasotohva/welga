<?php
declare(strict_types=1);

function welga_admin_settings(): array
{
    return welga_config('admin');
}

function welga_admin_credentials_path(): string
{
    return WELGA_STORAGE . '/data/admin.json';
}

function welga_admin_credentials(): array
{
    $path = welga_admin_credentials_path();
    if (!is_file($path)) {
        return [];
    }
    $data = json_decode((string)file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function welga_admin_is_configured(): bool
{
    return !empty(welga_admin_credentials()['password_hash']);
}

function welga_admin_write_credentials(string $password): bool
{
    if (strlen($password) < 12) {
        return false;
    }

    $path = welga_admin_credentials_path();
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
        return false;
    }

    $payload = [
        'username' => (string)(welga_admin_settings()['username'] ?? 'admin'),
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'updated_at' => date('c'),
    ];

    $tmp = tempnam($dir, '.welga-admin-');
    if ($tmp === false) {
        return false;
    }
    $ok = file_put_contents($tmp, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL, LOCK_EX) !== false;
    if ($ok) {
        @chmod($tmp, 0640);
        $ok = @rename($tmp, $path);
    }
    if (is_file($tmp)) {
        @unlink($tmp);
    }
    return $ok;
}

function welga_admin_authenticated(): bool
{
    if (empty($_SESSION['welga_admin']['authenticated'])) {
        return false;
    }

    $timeout = (int)(welga_admin_settings()['session_timeout'] ?? 1800);
    $last = (int)($_SESSION['welga_admin']['last_activity'] ?? 0);
    if ($last > 0 && time() - $last > $timeout) {
        unset($_SESSION['welga_admin']);
        return false;
    }

    $_SESSION['welga_admin']['last_activity'] = time();
    return true;
}

function welga_admin_login(string $username, string $password): bool
{
    $cfg = welga_admin_settings();
    $credentials = welga_admin_credentials();
    $expected = (string)($cfg['username'] ?? 'admin');

    if (!hash_equals($expected, $username)) {
        return false;
    }
    if (empty($credentials['password_hash']) || !password_verify($password, (string)$credentials['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['welga_admin'] = [
        'authenticated' => true,
        'username' => $username,
        'last_activity' => time(),
    ];
    return true;
}

function welga_admin_logout(): void
{
    unset($_SESSION['welga_admin']);
    session_regenerate_id(true);
}

function welga_admin_rate_path(): string
{
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    return WELGA_STORAGE . '/cache/admin-login-' . hash('sha256', $ip) . '.json';
}

function welga_admin_login_locked(): int
{
    $path = welga_admin_rate_path();
    if (!is_file($path)) {
        return 0;
    }
    $data = json_decode((string)file_get_contents($path), true);
    if (!is_array($data)) {
        return 0;
    }
    $until = (int)($data['locked_until'] ?? 0);
    if ($until <= time()) {
        @unlink($path);
        return 0;
    }
    return $until - time();
}

function welga_admin_register_failed_login(): void
{
    $cfg = welga_admin_settings();
    $path = welga_admin_rate_path();
    $data = is_file($path) ? (json_decode((string)file_get_contents($path), true) ?: []) : [];
    $window = (int)($cfg['lockout_seconds'] ?? 900);
    $first = (int)($data['first_attempt'] ?? time());

    if (time() - $first > $window) {
        $data = ['attempts' => 0, 'first_attempt' => time()];
    }

    $data['attempts'] = (int)($data['attempts'] ?? 0) + 1;
    $data['first_attempt'] = $data['first_attempt'] ?? time();
    if ($data['attempts'] >= (int)($cfg['max_login_attempts'] ?? 5)) {
        $data['locked_until'] = time() + $window;
    }

    @file_put_contents($path, json_encode($data), LOCK_EX);
}

function welga_admin_clear_login_attempts(): void
{
    @unlink(welga_admin_rate_path());
}
