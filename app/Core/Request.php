<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Request Utilities
 * Milestone 3: Foundation
 */
class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function isGet(): bool
    {
        return self::method() === 'GET';
    }

    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    public static function uri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH) ?? '/';
        return $uri;
    }

    public static function path(): string
    {
        // Clean path, ensure leading slash, no trailing double slashes
        $path = self::uri();
        $path = '/' . ltrim($path, '/');
        $path = preg_replace('#//+#', '/', $path);
        // Remove query string already via parse_url
        return $path;
    }

    public static function get(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    public static function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    public static function input(string $key, $default = null)
    {
        // Check POST, then GET, then JSON body
        if (isset($_POST[$key])) return $_POST[$key];
        if (isset($_GET[$key])) return $_GET[$key];

        $json = self::json();
        if (is_array($json) && isset($json[$key])) return $json[$key];

        return $default;
    }

    public static function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public static function json(): ?array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') === false) {
            return null;
        }

        $raw = file_get_contents('php://input');
        if (!$raw) return null;

        $data = json_decode($raw, true);
        return json_last_error() === JSON_ERROR_NONE ? $data : null;
    }

    public static function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public static function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public static function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    public static function validateMethod(array $allowed): bool
    {
        return in_array(self::method(), array_map('strtoupper', $allowed), true);
    }
}
