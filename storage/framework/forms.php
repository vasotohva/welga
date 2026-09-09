<?php
declare(strict_types=1);

function welga_form_client_fingerprint(string $bucket): string
{
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $agent = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 240);
    $session = session_id();
    return hash('sha256', $bucket . '|' . $ip . '|' . $agent . '|' . $session);
}

function welga_rate_limit_allow(string $bucket, int $limit, int $windowSeconds): bool
{
    $limit = max(1, $limit);
    $windowSeconds = max(60, $windowSeconds);
    $dir = WELGA_STORAGE . '/cache/rate-limit';
    if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) {
        // Fail closed for public form submission if rate-limit storage is unavailable.
        welga_log('security', 'Unable to create rate-limit directory.');
        return false;
    }

    $fingerprint = welga_form_client_fingerprint($bucket);
    $path = $dir . '/' . preg_replace('/[^a-z0-9_-]/i', '-', $bucket) . '-' . $fingerprint . '.json';
    $handle = @fopen($path, 'c+');
    if (!is_resource($handle)) {
        return false;
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            return false;
        }

        $raw = stream_get_contents($handle);
        $timestamps = is_string($raw) && $raw !== '' ? json_decode($raw, true) : [];
        if (!is_array($timestamps)) {
            $timestamps = [];
        }

        $now = time();
        $cutoff = $now - $windowSeconds;
        $timestamps = array_values(array_filter($timestamps, static fn($ts): bool => is_int($ts) && $ts > $cutoff));
        if (count($timestamps) >= $limit) {
            return false;
        }

        $timestamps[] = $now;
        rewind($handle);
        ftruncate($handle, 0);
        fwrite($handle, json_encode($timestamps, JSON_THROW_ON_ERROR));
        fflush($handle);
        return true;
    } catch (Throwable $e) {
        welga_log('security', 'Rate-limit storage failure.', ['error' => $e->getMessage()]);
        return false;
    } finally {
        @flock($handle, LOCK_UN);
        @fclose($handle);
    }
}

function welga_post_string(string $key, int $maxLength = 2000): string
{
    $value = trim((string)($_POST[$key] ?? ''));
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLength, 'UTF-8');
    }
    return substr($value, 0, $maxLength);
}

function welga_json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
