<?php
declare(strict_types=1);

function welga_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = welga_config('database');
    $database = trim((string)($cfg['database'] ?? ''));
    $username = trim((string)($cfg['username'] ?? ''));
    if ($database === '' || $username === '') {
        throw new RuntimeException('Database is not configured.');
    }

    $host = (string)($cfg['host'] ?? 'localhost');
    $port = (int)($cfg['port'] ?? 3306);
    $charset = (string)($cfg['charset'] ?? 'utf8mb4');
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $database, $charset);

    $pdo = new PDO($dsn, $username, (string)($cfg['password'] ?? ''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ]);

    return $pdo;
}

function welga_db_available(): bool
{
    try {
        welga_db()->query('SELECT 1');
        return true;
    } catch (Throwable $e) {
        welga_log('database', 'Database unavailable', ['error' => $e->getMessage()]);
        return false;
    }
}

function welga_db_table_exists(string $table): bool
{
    static $cache = [];
    $table = preg_replace('/[^a-z0-9_]/i', '', $table) ?: '';
    if ($table === '') {
        return false;
    }
    if (array_key_exists($table, $cache)) {
        return $cache[$table];
    }

    try {
        $row = welga_db_one(
            'SELECT 1 AS found FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table_name LIMIT 1',
            ['table_name' => $table]
        );
        return $cache[$table] = $row !== null;
    } catch (Throwable $e) {
        welga_log('database', 'Unable to inspect table capability', ['table' => $table, 'error' => $e->getMessage()]);
        return $cache[$table] = false;
    }
}

function welga_db_one(string $sql, array $params = []): ?array
{
    $stmt = welga_db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

function welga_db_all(string $sql, array $params = []): array
{
    $stmt = welga_db()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    return is_array($rows) ? $rows : [];
}

function welga_db_execute(string $sql, array $params = []): int
{
    $stmt = welga_db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}
