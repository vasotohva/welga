<?php
declare(strict_types=1);

function welga_config(string $group, ?string $key = null, mixed $default = null): mixed
{
    static $cache = [];

    if (!array_key_exists($group, $cache)) {
        $live = WELGA_STORAGE . '/config/' . $group . '.php';
        $example = WELGA_STORAGE . '/config/' . $group . '.example.php';
        $file = is_file($live) ? $live : (is_file($example) ? $example : null);
        $value = $file !== null ? require $file : [];
        $cache[$group] = is_array($value) ? $value : [];
    }

    if ($key === null) {
        return $cache[$group];
    }

    return $cache[$group][$key] ?? $default;
}
