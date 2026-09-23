<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Security Utilities — Foundation
 * Milestone 3
 * 
 * - Output escaping
 * - CSRF foundation
 * - Secure headers
 * - Safe redirects
 * - Input validation helpers
 */
class Security
{
    // Output escaping
    public static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function escapeAttr(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    // For trusted HTML (blog content) — should be purified on save, not just escaped
    public static function purifyHtml(string $html): string
    {
        // Basic allowlist purification (for Milestone 3 foundation, full HTMLPurifier in M4)
        // Allow: h2,h3,p,ul,ol,li,a,strong,em,blockquote,code,pre,br,span,div
        // Strip: script, iframe, object, embed, form, input, etc.
        // This is minimal, M4 will use HTMLPurifier library

        $allowedTags = '<h2><h3><p><ul><ol><li><a><strong><em><b><i><blockquote><code><pre><br><span><div><u><hr>';

        // Remove script and style tags completely
        $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
        $html = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $html);
        $html = preg_replace('#<iframe(.*?)>(.*?)</iframe>#is', '', $html);

        // Strip disallowed tags
        $html = strip_tags($html, $allowedTags);

        // Remove event handlers on* and javascript: URLs
        $html = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
        $html = preg_replace('/\s*on\w+\s*=\s*[^\s>]+/i', '', $html);
        $html = preg_replace('/href\s*=\s*["\']\s*javascript:[^"\']*["\']/i', 'href="#"', $html);

        return $html;
    }

    // CSRF
    public static function generateCsrfToken(): string
    {
        Session::start();
        $token = bin2hex(random_bytes(32));
        $_SESSION['_csrf_token'] = $token;
        $_SESSION['_csrf_token_time'] = time();
        return $token;
    }

    public static function getCsrfToken(): string
    {
        Session::start();
        if (!isset($_SESSION['_csrf_token'])) {
            return self::generateCsrfToken();
        }
        return $_SESSION['_csrf_token'];
    }

    public static function validateCsrfToken(?string $token): bool
    {
        Session::start();
        $stored = $_SESSION['_csrf_token'] ?? '';
        if (!$stored || !$token) {
            return false;
        }

        // Optional expiry: 1 hour
        $time = $_SESSION['_csrf_token_time'] ?? 0;
        if ((time() - $time) > 3600) {
            return false;
        }

        return hash_equals($stored, $token);
    }

    // Secure headers
    public static function setSecureHeaders(): void
    {
        if (headers_sent()) return;

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        // HSTS only if HTTPS
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    // Input validation
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validateSlug(string $slug): bool
    {
        return preg_match('/^[a-z0-9-]+$/', $slug) === 1;
    }

    public static function validateUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    // Safe path handling for media
    public static function sanitizeFilename(string $filename): string
    {
        // Remove path, null bytes, special chars
        $filename = basename($filename);
        $filename = str_replace("\0", '', $filename);
        $filename = preg_replace('/[^a-zA-Z0-9-_.]/', '-', $filename);
        $filename = preg_replace('/-+/', '-', $filename);
        $filename = trim($filename, '-.');

        // Prevent double extension php
        $filename = preg_replace('/\.(php|phtml|php3|php4|php5|exe|sh|bat)$/i', '.txt', $filename);

        // Ensure not empty
        if (empty($filename)) {
            $filename = 'file-' . bin2hex(random_bytes(4));
        }

        return strtolower($filename);
    }

    public static function isSafePath(string $path, string $baseDir): bool
    {
        $realBase = realpath($baseDir);
        $realPath = realpath($path);

        if (!$realBase || !$realPath) {
            // If file doesn't exist yet, check dirname
            $realPath = realpath(dirname($path));
            if (!$realPath) return false;
        }

        return strpos($realPath, $realBase) === 0;
    }

    // Password helpers (bcrypt)
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT);
    }
}
