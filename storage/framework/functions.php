<?php
declare(strict_types=1);

function welga_escape(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function welga_csrf_token(): string
{
    $key = (string)welga_config('app', 'csrf_key', 'welga_csrf');
    if (empty($_SESSION[$key])) {
        $_SESSION[$key] = bin2hex(random_bytes(24));
    }
    return (string)$_SESSION[$key];
}

function welga_verify_csrf(?string $token): bool
{
    $key = (string)welga_config('app', 'csrf_key', 'welga_csrf');
    return is_string($token)
        && isset($_SESSION[$key])
        && hash_equals((string)$_SESSION[$key], $token);
}

function welga_redirect(string $url, int $status = 303): never
{
    header('Location: ' . $url, true, $status);
    exit;
}

function welga_request_path(): string
{
    $uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $path = (string)(parse_url($uri, PHP_URL_PATH) ?: '/');
    return '/' . ltrim(rawurldecode($path), '/');
}

function welga_render(string $view, array $data = [], int $status = 200): never
{
    $viewFile = WELGA_STORAGE . '/views/' . trim($view, '/') . '.php';
    if (!is_file($viewFile)) {
        throw new RuntimeException('View not found: ' . $view);
    }

    http_response_code($status);
    extract($data, EXTR_SKIP);
    require WELGA_STORAGE . '/views/layout.php';
    exit;
}

function welga_log(string $channel, string $message, array $context = []): void
{
    $dir = WELGA_STORAGE . '/logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }

    $line = '[' . date('Y-m-d H:i:s T') . '] ' . $message;
    if ($context !== []) {
        $json = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($json)) {
            $line .= ' ' . $json;
        }
    }
    @file_put_contents($dir . '/' . preg_replace('/[^a-z0-9_-]/i', '-', $channel) . '.log', $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}
