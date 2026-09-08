<?php
declare(strict_types=1);

require __DIR__ . '/../../storage/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    welga_json_response(['ok' => false, 'code' => 'method_not_allowed'], 405);
}

// Honeypot: respond as if accepted, but do not send or log personal payload.
if (welga_post_string('website', 240) !== '') {
    welga_json_response(['ok' => true, 'code' => 'accepted']);
}

$csrf = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf'] ?? '');
if (!welga_verify_csrf($csrf)) {
    welga_json_response(['ok' => false, 'code' => 'csrf'], 419);
}

if (!welga_rate_limit_allow('product-inquiry', 5, 3600)) {
    welga_json_response(['ok' => false, 'code' => 'rate_limited'], 429);
}

$productId = (int)($_POST['product_id'] ?? 0);
$name = welga_post_string('name', 120);
$emailRaw = welga_post_string('email', 190);
$email = filter_var($emailRaw, FILTER_VALIDATE_EMAIL) ?: '';
$phone = welga_post_string('phone', 80);
$message = welga_post_string('message', 5000);
$selectedOptions = welga_post_string('selected_options', 1500);
$selectedUpholstery = welga_post_string('selected_upholstery', 700);
$languageCode = preg_replace('/[^a-z-]/i', '', welga_post_string('language', 10)) ?: 'bg';
$language = welga_language_by_code($languageCode) ?? welga_default_language();
$languageId = (int)$language['language_id'];

if ($productId < 1 || strlen($name) < 2 || $email === '') {
    welga_json_response(['ok' => false, 'code' => 'validation'], 422);
}

if (!welga_db_available()) {
    welga_json_response(['ok' => false, 'code' => 'service_unavailable'], 503);
}

$product = welga_catalog_product($productId, $languageId);
if ($product === null && $languageId !== (int)welga_default_language()['language_id']) {
    $product = welga_catalog_product($productId, (int)welga_default_language()['language_id']);
}
if ($product === null) {
    welga_json_response(['ok' => false, 'code' => 'product_not_found'], 404);
}

$model = (string)($product['model'] ?? '');
$productName = (string)($product['name'] ?? $model);
$subject = 'WELGA product inquiry · ' . ($model !== '' ? $model : $productName);

$lines = [
    'WELGA — product inquiry',
    '',
    'Product: ' . $productName,
    'Model: ' . $model,
    'Product ID: ' . $productId,
    'Language: ' . (string)$language['code'],
    '',
    'Name: ' . $name,
    'Email: ' . $email,
    'Phone: ' . ($phone !== '' ? $phone : '—'),
];

if ($selectedOptions !== '') {
    $lines[] = '';
    $lines[] = 'Selected options:';
    $lines[] = $selectedOptions;
}
if ($selectedUpholstery !== '') {
    $lines[] = '';
    $lines[] = 'Selected upholstery:';
    $lines[] = $selectedUpholstery;
}
if ($message !== '') {
    $lines[] = '';
    $lines[] = 'Message:';
    $lines[] = $message;
}
$lines[] = '';
$lines[] = 'Sent: ' . date('Y-m-d H:i:s T');

$sent = welga_send_text_mail($subject, implode("\n", $lines), $email, $name);
if (!$sent) {
    welga_json_response(['ok' => false, 'code' => 'delivery_failed'], 503);
}

welga_json_response(['ok' => true, 'code' => 'sent']);
