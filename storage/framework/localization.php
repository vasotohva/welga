<?php
declare(strict_types=1);

function welga_languages(): array
{
    static $languages = null;
    if (is_array($languages)) {
        return $languages;
    }

    try {
        if (welga_db_available()) {
            $rows = welga_db_all('SELECT language_id, code, locale, name, native_name, is_default, sort_order FROM languages WHERE status = 1 ORDER BY sort_order, language_id');
            if ($rows !== []) {
                return $languages = $rows;
            }
        }
    } catch (Throwable) {
        // Bootstrap fallback below.
    }

    return $languages = [
        ['language_id' => 1, 'code' => 'bg', 'locale' => 'bg-BG', 'name' => 'Bulgarian', 'native_name' => 'Български', 'is_default' => 1, 'sort_order' => 10],
        ['language_id' => 2, 'code' => 'en', 'locale' => 'en-GB', 'name' => 'English', 'native_name' => 'English', 'is_default' => 0, 'sort_order' => 20],
        ['language_id' => 3, 'code' => 'de', 'locale' => 'de-DE', 'name' => 'German', 'native_name' => 'Deutsch', 'is_default' => 0, 'sort_order' => 30],
    ];
}

function welga_language_by_code(string $code): ?array
{
    foreach (welga_languages() as $language) {
        if (($language['code'] ?? '') === $code) {
            return $language;
        }
    }
    return null;
}

function welga_default_language(): array
{
    foreach (welga_languages() as $language) {
        if (!empty($language['is_default'])) {
            return $language;
        }
    }
    return welga_languages()[0];
}

function welga_language_context(string $requestPath): array
{
    $trimmed = trim($requestPath, '/');
    $segments = $trimmed === '' ? [] : explode('/', $trimmed);
    $default = welga_default_language();
    $language = $default;

    if ($segments !== []) {
        $candidate = welga_language_by_code((string)$segments[0]);
        if ($candidate !== null && ($candidate['code'] ?? '') !== ($default['code'] ?? '')) {
            $language = $candidate;
            array_shift($segments);
        }
    }

    return [
        'language' => $language,
        'path' => implode('/', $segments),
        'prefix' => ($language['code'] ?? '') === ($default['code'] ?? '') ? '' : '/' . $language['code'],
    ];
}

function welga_url(string $path = '', ?string $languageCode = null): string
{
    $language = $languageCode !== null ? welga_language_by_code($languageCode) : null;
    $language ??= welga_default_language();
    $default = welga_default_language();
    $prefix = ($language['code'] ?? '') === ($default['code'] ?? '') ? '' : '/' . $language['code'];
    $path = trim($path, '/');
    return $prefix . ($path === '' ? '/' : '/' . $path . '/');
}
