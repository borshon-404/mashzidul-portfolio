<?php
declare(strict_types=1);

namespace App\Security;

use App\Core\Session;

/**
 * CSRF Protection
 * Milestone 3: Foundation
 */
class Csrf
{
    public static function generate(): string
    {
        Session::start();
        $token = bin2hex(random_bytes(32));
        $_SESSION['_csrf_token'] = $token;
        $_SESSION['_csrf_token_time'] = time();
        return $token;
    }

    public static function getToken(): string
    {
        Session::start();
        return $_SESSION['_csrf_token'] ?? self::generate();
    }

    public static function validate(?string $token): bool
    {
        Session::start();
        $stored = $_SESSION['_csrf_token'] ?? '';
        if (!$stored || !$token) return false;
        $time = $_SESSION['_csrf_token_time'] ?? 0;
        if ((time() - $time) > 3600) return false; // 1 hour expiry
        return hash_equals($stored, $token);
    }

    public static function field(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
