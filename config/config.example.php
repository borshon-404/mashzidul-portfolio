<?php
/**
 * MTB Portfolio — Config Example
 * Milestone 3: PHP Backend Foundation
 * 
 * Copy to config.php (outside public_html in production):
 *   cp config/config.example.php config/config.php
 *   # or for cPanel: cp config/config.example.php ../config.php
 * 
 * config.php must be gitignored, 600 perms, never committed.
 * 
 * cPanel typical paths:
 *   /home/username/config.php (outside public_html)
 *   /home/username/private/config.php
 *   /home/username/app/Config/config.php
 */

return [
    // -----------------------------------------------------------------------
    // Environment
    // -----------------------------------------------------------------------
    'env' => 'production', // production | development | staging
    'debug' => false, // true in dev, false in production (display_errors Off)
    'site_domain' => 'https://mashzidultanun.com', // no trailing slash
    'app_url' => 'https://mashzidultanun.com',

    // -----------------------------------------------------------------------
    // Database — MySQL 8+ / MariaDB 10.6+ — PDO
    // -----------------------------------------------------------------------
    'db' => [
        'host' => 'localhost', // cPanel: localhost
        'name' => 'username_portfolio', // cPanel actual: username_portfolio (e.g., borshonba_portfolio)
        'user' => 'username_admin',     // cPanel actual: username_admin
        'pass' => 'STRONG_PASSWORD_20+_CHARS', // From MySQL Databases wizard
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],

    // -----------------------------------------------------------------------
    // Session — secure, HttpOnly, SameSite, strict_mode
    // -----------------------------------------------------------------------
    'session' => [
        'name' => 'MTBSESSID',
        'lifetime' => 3600, // 1 hour idle
        'path' => '/',
        'domain' => '', // empty for current domain, or .mashzidultanun.com for subdomain
        'secure' => true, // true in production HTTPS, false in local http
        'httponly' => true,
        'samesite' => 'Lax', // Lax or Strict
        'use_strict_mode' => true,
    ],

    // -----------------------------------------------------------------------
    // Security
    // -----------------------------------------------------------------------
    'security' => [
        'secret_key' => 'CHANGE_THIS_TO_RANDOM_32+_CHARS_BIN2HEX', // bin2hex(random_bytes(32))
        'csrf_token_name' => '_csrf',
        'hash_algo' => PASSWORD_BCRYPT, // or PASSWORD_ARGON2ID if available
    ],

    // -----------------------------------------------------------------------
    // Uploads — media foundation
    // -----------------------------------------------------------------------
    'uploads' => [
        'dir' => __DIR__ . '/../public_html/uploads', // or __DIR__ . '/../../public_html/uploads' if config outside
        'url' => '/uploads',
        'max_size' => 5 * 1024 * 1024, // 5MB
        'allowed_mimes' => ['image/jpeg','image/png','image/webp','image/svg+xml'],
        'allowed_extensions' => ['jpg','jpeg','png','webp','svg'],
        'variants' => [480, 720, 1114, 1536], // WebP widths to generate
        'quality' => 82,
    ],

    // -----------------------------------------------------------------------
    // Mail — placeholders, never expose in frontend JS
    // -----------------------------------------------------------------------
    'mail' => [
        'from_email' => 'noreply@mashzidultanun.com',
        'from_name' => 'Mashzidul Tanun Borshon',
        'admin_email' => 'mail@mashzidultanun.com',
        'smtp_host' => 'mail.mashzidultanun.com', // cPanel email
        'smtp_port' => 465,
        'smtp_user' => 'noreply@mashzidultanun.com',
        'smtp_pass' => 'SMTP_PASSWORD_HERE',
        'smtp_secure' => 'ssl', // ssl or tls
    ],

    // -----------------------------------------------------------------------
    // Rate Limiting
    // -----------------------------------------------------------------------
    'rate_limit' => [
        'login_max_attempts' => 5,
        'login_window_minutes' => 15,
        'contact_max_per_hour' => 5,
    ],
];
