<?php
declare(strict_types=1);

return [
    // Copy to storage/config/mail.php on the server and fill real credentials.
    'enabled' => false,
    'host' => 'smtp.example.com',
    'port' => 587,
    // tls = STARTTLS, ssl = implicit TLS (usually port 465), none = plain SMTP.
    'encryption' => 'tls',
    'username' => '',
    'password' => '',
    'from_email' => 'website@welga.com',
    'from_name' => 'WELGA Website',
    'recipient' => '',
    'timeout' => 12,
];
