<?php
declare(strict_types=1);

namespace App\Config;

/**
 * Secure Configuration System
 * Milestone 3: Foundation
 * 
 * - Credentials outside public web root where practical
 * - Production/local separation via env
 * - Never expose secrets via public responses
 */
class Config
{
    private static ?array $config = null;
    private static string $env = 'production';

    public static function load(string $configPath = null): array
    {
        if (self::$config !== null) {
            return self::$config;
        }

        // Try multiple locations for config.php (outside public_html first)
        $possiblePaths = [
            $configPath,
            __DIR__ . '/../../config/config.php', // repo/config/config.php
            __DIR__ . '/../../private/config.php',
            __DIR__ . '/../../../config.php', // outside public_html: /home/username/config.php
            __DIR__ . '/config.php',
            dirname(__DIR__, 2) . '/config/config.php',
        ];

        $foundPath = null;
        foreach ($possiblePaths as $path) {
            if ($path && file_exists($path)) {
                $foundPath = $path;
                break;
            }
        }

        if (!$foundPath) {
            // Fallback to example for dev (with warning)
            $example = __DIR__ . '/../../config/config.example.php';
            if (file_exists($example)) {
                self::$config = require $example;
                self::$env = self::$config['env'] ?? 'production';
                // In production, this should not happen
                error_log("Config not found, using example: $example");
                return self::$config;
            }
            throw new \RuntimeException("Configuration file not found. Tried: " . implode(', ', $possiblePaths));
        }

        $cfg = require $foundPath;
        if (!is_array($cfg)) {
            throw new \RuntimeException("Config file must return array: $foundPath");
        }

        self::$config = $cfg;
        self::$env = $cfg['env'] ?? 'production';

        return self::$config;
    }

    public static function get(string $key, $default = null)
    {
        $cfg = self::load();
        $keys = explode('.', $key);
        $value = $cfg;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public static function env(): string
    {
        return self::$env;
    }

    public static function isProduction(): bool
    {
        return self::env() === 'production';
    }

    public static function isDevelopment(): bool
    {
        return self::env() === 'development';
    }

    public static function isDebug(): bool
    {
        return (bool) self::get('debug', false);
    }

    // Security: Never expose secrets
    public static function getSafeConfig(): array
    {
        $cfg = self::load();
        // Remove sensitive keys
        $safe = $cfg;
        unset($safe['db']['pass']);
        unset($safe['mail']['smtp_pass']);
        unset($safe['security']['secret_key']);
        return $safe;
    }
}
