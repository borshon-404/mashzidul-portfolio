<?php
declare(strict_types=1);

namespace App\Core;

use App\Config\Config;

/**
 * Response Utilities
 * Milestone 3: Foundation
 */
class Response
{
    public static function html(string $content, int $status = 200, array $headers = []): void
    {
        http_response_code($status);
        foreach ($headers as $k => $v) {
            header("$k: $v");
        }
        header('Content-Type: text/html; charset=utf-8');
        echo $content;
        exit;
    }

    public static function json($data, int $status = 200, array $headers = []): void
    {
        http_response_code($status);
        foreach ($headers as $k => $v) {
            header("$k: $v");
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function redirect(string $url, int $status = 302): void
    {
        // Safe redirect: only allow relative or same-domain absolute
        if (!self::isSafeRedirect($url)) {
            $url = '/';
        }
        http_response_code($status);
        header("Location: $url");
        exit;
    }

    public static function redirect301(string $url): void
    {
        self::redirect($url, 301);
    }

    public static function isSafeRedirect(string $url): bool
    {
        // Allow relative URLs starting with /
        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            return true;
        }

        // Allow absolute URLs only if same domain
        $domain = Config::get('site_domain', '');
        if ($domain && strpos($url, $domain) === 0) {
            return true;
        }

        // Allow app_url
        $appUrl = Config::get('app_url', '');
        if ($appUrl && strpos($url, $appUrl) === 0) {
            return true;
        }

        return false;
    }

    public static function status(int $code, string $message = ''): void
    {
        http_response_code($code);
        if ($message) {
            echo $message;
        }
        exit;
    }

    public static function notFound(string $message = 'Not Found'): void
    {
        self::status(404, $message);
    }

    public static function forbidden(string $message = 'Forbidden'): void
    {
        self::status(403, $message);
    }

    public static function methodNotAllowed(string $message = 'Method Not Allowed'): void
    {
        self::status(405, $message);
    }
}
