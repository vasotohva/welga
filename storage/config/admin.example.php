<?php
declare(strict_types=1);

return [
    'username' => 'admin',
    // Fill only in storage/config/admin.php on the server. Never commit a real setup hash.
    'setup_key_hash' => '',
    'session_timeout' => 1800,
    'max_login_attempts' => 5,
    'lockout_seconds' => 900,
];
