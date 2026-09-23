<?php
declare(strict_types=1);

namespace App\Security;

use App\Core\Session;
use App\Core\Database;

/**
 * Rate Limiter Foundation
 * Milestone 3: Foundation
 * 
 * - Login: 5 attempts per 15 min per IP
 * - Contact: 5 per hour per IP
 * - Uses session for M3 foundation, DB table login_attempts in M4
 */
class RateLimiter
{
    public static function isLoginAllowed(string $ip): bool
    {
        Session::start();
        $key = "login_attempts_$ip";
        $data = $_SESSION[$key] ?? ['count'=>0, 'time'=>time()];

        if ((time() - $data['time']) > 900) { // 15 min window
            return true;
        }

        return $data['count'] < 5;
    }

    public static function recordLoginAttempt(string $ip, bool $success): void
    {
        Session::start();
        $key = "login_attempts_$ip";

        if ($success) {
            unset($_SESSION[$key]);
            return;
        }

        $data = $_SESSION[$key] ?? ['count'=>0, 'time'=>time()];
        if ((time() - $data['time']) > 900) {
            $data = ['count'=>0, 'time'=>time()];
        }
        $data['count']++;
        $data['time'] = time();
        $_SESSION[$key] = $data;
    }

    public static function isContactAllowed(string $ip): bool
    {
        Session::start();
        $key = "contact_attempts_$ip";
        $data = $_SESSION[$key] ?? ['count'=>0, 'time'=>time()];

        if ((time() - $data['time']) > 3600) { // 1 hour window
            return true;
        }

        return $data['count'] < 5;
    }

    public static function recordContactAttempt(string $ip): void
    {
        Session::start();
        $key = "contact_attempts_$ip";
        $data = $_SESSION[$key] ?? ['count'=>0, 'time'=>time()];
        if ((time() - $data['time']) > 3600) {
            $data = ['count'=>0, 'time'=>time()];
        }
        $data['count']++;
        $data['time'] = time();
        $_SESSION[$key] = $data;
    }

    // For future DB implementation
    public static function checkDbRateLimit(string $ip, string $type, int $maxAttempts, int $windowMinutes): bool
    {
        // Will be implemented in M4 with login_attempts table
        // For M3, use session fallback
        if ($type === 'login') return self::isLoginAllowed($ip);
        if ($type === 'contact') return self::isContactAllowed($ip);
        return true;
    }
}
