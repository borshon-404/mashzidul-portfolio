<?php
declare(strict_types=1);

namespace App\Core;

use App\Config\Config;

/**
 * Secure Session Configuration
 * Milestone 3: Foundation
 * 
 * - HttpOnly, Secure, SameSite
 * - use_strict_mode
 * - Regenerate ID after auth
 */
class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started) return;
        if (session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $sessionConfig = Config::get('session', []);

        $name = $sessionConfig['name'] ?? 'MTBSESSID';
        $lifetime = $sessionConfig['lifetime'] ?? 3600;
        $path = $sessionConfig['path'] ?? '/';
        $domain = $sessionConfig['domain'] ?? '';
        $secure = $sessionConfig['secure'] ?? true;
        $httponly = $sessionConfig['httponly'] ?? true;
        $samesite = $sessionConfig['samesite'] ?? 'Lax';
        $useStrict = $sessionConfig['use_strict_mode'] ?? true;

        session_name($name);

        // Set cookie params
        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path' => $path,
                'domain' => $domain,
                'secure' => $secure,
                'httponly' => $httponly,
                'samesite' => $samesite,
            ]);
        } else {
            // Fallback for older PHP (should not happen on 8.2+)
            session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
        }

        ini_set('session.use_strict_mode', $useStrict ? '1' : '0');
        ini_set('session.cookie_httponly', $httponly ? '1' : '0');
        ini_set('session.cookie_secure', $secure ? '1' : '0');
        ini_set('session.gc_maxlifetime', (string)$lifetime);
        ini_set('session.use_only_cookies', '1');

        session_start();
        self::$started = true;

        // Fingerprint for hijacking detection (IP + User-Agent, with tolerance)
        if (!isset($_SESSION['_fingerprint'])) {
            $_SESSION['_fingerprint'] = self::generateFingerprint();
        } else {
            // Optional: Check fingerprint, but allow some tolerance for IP changes
            // For now, only check User-Agent
            $currentUA = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $storedUA = $_SESSION['_ua'] ?? '';
            if ($storedUA && $storedUA !== $currentUA) {
                // Potential hijacking, destroy session
                self::destroy();
                self::start();
            }
        }
        $_SESSION['_ua'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['_last_activity'] = time();
    }

    public static function generateFingerprint(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $secret = Config::get('security.secret_key', 'default_secret');
        return hash('sha256', $ip . $ua . $secret);
    }

    public static function get(string $key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
            session_destroy();
        }
        self::$started = false;
    }

    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    public static function isExpired(int $maxLifetime = 3600): bool
    {
        self::start();
        $last = $_SESSION['_last_activity'] ?? 0;
        return (time() - $last) > $maxLifetime;
    }
}
