<?php
declare(strict_types=1);

require __DIR__ . '/../../storage/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    welga_json_response(['ok' => false, 'code' => 'method_not_allowed'], 405);
}

welga_json_response(['ok' => true, 'token' => welga_csrf_token()]);
