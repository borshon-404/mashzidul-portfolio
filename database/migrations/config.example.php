<?php
/**
 * MTB Portfolio — Migration Config Example
 * Milestone 2: Data Migration Only
 * 
 * Copy this file to config.php and fill with real cPanel credentials.
 * config.php is gitignored and must NOT be committed.
 * 
 * Place config.php outside public_html if possible, e.g. /home/username/config.php
 * or /home/username/private/config.php
 * 
 * For local testing with XAMPP:
 *   db_host = localhost
 *   db_name = portfolio
 *   db_user = root
 *   db_pass = (empty or root password)
 */

return [
    // Database — cPanel MySQL
    'db_host' => 'localhost',
    'db_name' => 'borshonba_portfolio', // cPanel actual name: username_portfolio
    'db_user' => 'borshonba_admin',     // cPanel actual user: username_admin
    'db_pass' => 'STRONG_PASSWORD_HERE', // 20+ chars, from MySQL Databases wizard
    'db_charset' => 'utf8mb4',
    'db_collation' => 'utf8mb4_unicode_ci',

    // Site
    'site_domain' => 'https://mashzidultanun.com',

    // Paths — absolute or relative to this file
    'content_json_path' => __DIR__ . '/../../site_src/content.json',
    'blog_json_path' => __DIR__ . '/../../site_src/blog_posts_real.json',
    'blog_images_dir' => __DIR__ . '/../../assets/img/blog',
    'brand_images_dir' => __DIR__ . '/../../assets/img/brand',
    'portrait_images_dir' => __DIR__ . '/../../assets/img/portrait',

    // Migration options
    'dry_run' => false, // true = validate only, no DB writes
    'verbose' => true,
];
