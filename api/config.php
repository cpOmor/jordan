<?php

// ── Load .env first ────────────────────────────────────────────────────────
(static function () {
    $path = __DIR__ . '/.env';
    if (!is_file($path) || !is_readable($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }
        $k = trim($parts[0]);
        $v = trim(trim($parts[1]), "\"'");
        if ($k !== '' && (getenv($k) === false || getenv($k) === '')) {
            putenv($k . '=' . $v);
            $_ENV[$k] = $v;
            $_SERVER[$k] = $v;
        }
    }
})();

// ── Constants sourced from .env (with hardcoded fallback) ──────────────────
define('JORDAN_MYSQL_HOST', getenv('JORDAN_MYSQL_HOST') ?: '127.0.0.1');
define('JORDAN_MYSQL_PORT', getenv('JORDAN_MYSQL_PORT') ?: '3306');
define('JORDAN_MYSQL_DB', getenv('JORDAN_MYSQL_DB') ?: 'visaguro_e_visa');
define('JORDAN_MYSQL_USER', getenv('JORDAN_MYSQL_USER') ?: 'visaguro_user');
define('JORDAN_MYSQL_PASS', getenv('JORDAN_MYSQL_PASS') !== false ? getenv('JORDAN_MYSQL_PASS') : 'Evis@1234!');
define('JORDAN_MYSQL_CHARSET', getenv('JORDAN_MYSQL_CHARSET') ?: 'utf8mb4');
define('JORDAN_MYSQL_TABLE', getenv('JORDAN_MYSQL_TABLE') ?: 'jordan_evisa');
define('JORDAN_ALLOWED_ORIGIN', getenv('JORDAN_ALLOWED_ORIGIN') ?: '*');
define('JORDAN_DOMAIN', rtrim(getenv('JORDAN_DOMAIN') ?: '/', '/'));
define('JORDAN_QR_SECRET', getenv('JORDAN_QR_SECRET') ?: '');
define('JORDAN_QR_TTL', getenv('JORDAN_QR_TTL') ?: '604800');

// ── Helper functions ───────────────────────────────────────────────────────
function jordan_env(string $key, string $default = ''): string
{
    $value = getenv($key);
    if ($value === false && isset($_ENV[$key])) {
        $value = (string) $_ENV[$key];
    }
    if (($value === false || $value === '') && isset($_SERVER[$key])) {
        $value = (string) $_SERVER[$key];
    }
    if ($value === false || $value === '') {
        return $default;
    }
    return $value;
}

function jordan_mysql_host(): string
{
    return JORDAN_MYSQL_HOST;
}

function jordan_mysql_port(): string
{
    return JORDAN_MYSQL_PORT;
}

function jordan_mysql_db(): string
{
    return JORDAN_MYSQL_DB;
}

function jordan_mysql_user(): string
{
    return JORDAN_MYSQL_USER;
}

function jordan_mysql_pass(): string
{
    return JORDAN_MYSQL_PASS;
}

function jordan_mysql_charset(): string
{
    return JORDAN_MYSQL_CHARSET;
}

function jordan_mysql_table(): string
{
    return JORDAN_MYSQL_TABLE;
}

function jordan_allowed_origin(): string
{
    return JORDAN_ALLOWED_ORIGIN;
}

function jordan_domain(): string
{
    return JORDAN_DOMAIN;
}

function jordan_qr_secret(): string
{
    return JORDAN_QR_SECRET;
}

function jordan_qr_ttl_seconds(): int
{
    $value = (int) JORDAN_QR_TTL;
    return $value > 0 ? $value : 604800;
}
