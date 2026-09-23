<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Authentication Foundation
 * Milestone 3: Foundation only, full admin UI in M4
 * 
 * - Session init
 * - Password verification helper
 * - requireAdmin() middleware
 * - guest-only helper
 * - Rate-limit foundation
 */
class Auth
{
    public static function init(): void
    {
        Session::start();
    }

    public static function check(): bool
    {
        Session::start();
        return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
    }

    public static function isAdmin(): bool
    {
        Session::start();
        return ($_SESSION['user_role'] ?? '') === 'admin';
    }

    public static function isEditor(): bool
    {
        Session::start();
        $role = $_SESSION['user_role'] ?? '';
        return $role === 'admin' || $role === 'editor';
    }

    public static function userId(): ?int
    {
        Session::start();
        return $_SESSION['user_id'] ?? null;
    }

    public static function user(): ?array
    {
        Session::start();
        if (!self::check()) return null;

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'role' => $_SESSION['user_role'] ?? null,
        ];
    }

    public static function login(array $user): void
    {
        Session::start();
        Session::regenerate();

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'] ?? '';
        $_SESSION['user_role'] = $user['role'] ?? 'admin';
        $_SESSION['logged_in_at'] = time();
        $_SESSION['_fingerprint'] = Session::generateFingerprint();

        // Update last_login_at in DB (optional, handled by controller)
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    // Middleware helpers
    public static function requireAdmin(): void
    {
        self::init();
        if (!self::check()) {
            // Not logged in → redirect to login
            Response::redirect('/admin/login');
        }

        if (!self::isAdmin() && !self::isEditor()) {
            // Logged in but not admin/editor → 403
            http_response_code(403);
            $view = new View();
            echo $view->renderError(403, 'Forbidden: Admin access required');
            exit;
        }

        // Check session expiry
        if (Session::isExpired(3600)) {
            self::logout();
            Response::redirect('/admin/login?expired=1');
        }
    }

    public static function requireGuest(): void
    {
        self::init();
        if (self::check()) {
            // Already logged in → redirect to dashboard
            Response::redirect('/admin/dashboard');
        }
    }

    // Password helpers (delegate to Security)
    public static function hashPassword(string $password): string
    {
        return Security::hashPassword($password);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return Security::verifyPassword($password, $hash);
    }

    // Rate-limit foundation (uses DB or file, simple for M3)
    public static function checkLoginRateLimit(string $ip, string $email): bool
    {
        // For M3 foundation, we will implement in M4 with DB table login_attempts
        // Here we provide helper structure

        Session::start();
        $key = "login_attempts_$ip";
        $attempts = $_SESSION[$key] ?? ['count'=>0, 'time'=>time()];

        // Reset if window passed (15 min)
        if ((time() - $attempts['time']) > 900) {
            $attempts = ['count'=>0, 'time'=>time()];
        }

        if ($attempts['count'] >= 5) {
            return false; // Rate limited
        }

        return true;
    }

    public static function recordLoginAttempt(string $ip, string $email, bool $success): void
    {
        Session::start();
        $key = "login_attempts_$ip";

        if ($success) {
            unset($_SESSION[$key]);
            return;
        }

        $attempts = $_SESSION[$key] ?? ['count'=>0, 'time'=>time()];
        $attempts['count']++;
        $attempts['time'] = time();
        $_SESSION[$key] = $attempts;
    }
}
