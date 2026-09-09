<?php
declare(strict_types=1);

require __DIR__ . '/../../storage/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    welga_json_response(['ok' => false, 'code' => 'method_not_allowed'], 405);
}

$productId = (int)($_GET['product_id'] ?? 0);
$languageCode = preg_replace('/[^a-z-]/i', '', (string)($_GET['lang'] ?? 'bg')) ?: 'bg';
$language = welga_language_by_code($languageCode) ?? welga_default_language();

if ($productId < 1) {
    welga_json_response(['ok' => false, 'code' => 'validation'], 422);
}

if (!welga_db_available()) {
    welga_json_response(['ok' => false, 'code' => 'service_unavailable'], 503);
}

$product = welga_catalog_product($productId, (int)$language['language_id']);
if ($product === null) {
    welga_json_response(['ok' => false, 'code' => 'product_not_found'], 404);
}

welga_json_response([
    'ok' => true,
    'product_id' => $productId,
    'configurations' => welga_catalog_product_configurations($productId, (int)$language['language_id']),
]);
