<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Security;
use App\Core\Session;
use App\Core\View;
use App\Models\User;
use App\Security\Csrf;
use App\Security\RateLimiter;

/**
 * Admin Authentication Controller
 * Milestone 4.1 — Login, Logout
 * 
 * Routes:
 * GET  /admin/login
 * POST /admin/login
 * POST /admin/logout
 */
class AuthController
{
    private View $view;
    private User $userModel;

    public function __construct()
    {
        $this->view = new View(__DIR__ . '/../../Views');
        $this->userModel = new User();
        Security::setSecureHeaders();
    }

    // GET /admin/login
    public function showLogin(): void
    {
        Session::start();

        // If already authenticated, redirect to dashboard
        if (Auth::check()) {
            Response::redirect('/admin/dashboard');
        }

        $csrfToken = Csrf::getToken();
        $error = Session::get('login_error');
        $oldEmail = Session::get('old_email', '');

        // Clear flash after reading
        Session::remove('login_error');
        Session::remove('old_email');

        $html = $this->view->render('admin/login', [
            'csrf_token' => $csrfToken,
            'error' => $error,
            'old_email' => $oldEmail,
            'title' => 'Admin Login',
        ]);

        echo $html;
        exit;
    }

    // POST /admin/login
    public function login(): void
    {
        Session::start();

        // Method validation
        if (!Request::isPost()) {
            Response::status(405, 'Method Not Allowed');
        }

        // If already authenticated, redirect
        if (Auth::check()) {
            Response::redirect('/admin/dashboard');
        }

        $ip = Request::ip();
        $email = trim((string) Request::post('email', ''));
        $password = (string) Request::post('password', '');
        $csrf = (string) Request::post('_csrf', '');

        // CSRF validation
        if (!Csrf::validate($csrf)) {
            Session::set('login_error', 'Invalid security token. Please try again.');
            Session::set('old_email', $email);
            Response::redirect('/admin/login');
        }

        // Rate limiting — 5 failed attempts per 15 minutes per IP
        if (!RateLimiter::isLoginAllowed($ip)) {
            // Generic message, don't reveal rate limit details too much
            Session::set('login_error', 'Too many failed attempts. Please try again in 15 minutes.');
            Session::set('old_email', $email);
            // Add delay to slow brute force
            sleep(2);
            Response::redirect('/admin/login');
        }

        // Validation — generic errors, no user enumeration
        if (empty($email) || empty($password)) {
            RateLimiter::recordLoginAttempt($ip, false);
            Session::set('login_error', 'Email and password are required.');
            Session::set('old_email', $email);
            Response::redirect('/admin/login');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            RateLimiter::recordLoginAttempt($ip, false);
            Session::set('login_error', 'Invalid email or password.');
            Session::set('old_email', $email);
            Response::redirect('/admin/login');
        }

        // Attempt to find user — do not reveal if email exists
        try {
            $user = $this->userModel->findByEmail($email);

            // Generic failure message for both cases (user not found or password wrong)
            // To prevent timing attacks, we still do password_verify even if user not found
            // by using a dummy hash
            $dummyHash = '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
            $hashToVerify = $user ? $user['password_hash'] : $dummyHash;

            $passwordValid = Security::verifyPassword($password, $hashToWrite = $hashToVerify);

            // If user not found or password invalid → generic error
            if (!$user || !$passwordValid) {
                RateLimiter::recordLoginAttempt($ip, false);
                // Small delay to make timing more consistent and slow brute force
                usleep(500000); // 0.5 sec
                Session::set('login_error', 'Invalid email or password.');
                Session::set('old_email', $email);
                Response::redirect('/admin/login');
            }

            // Check if user is active
            if ((int)($user['is_active'] ?? 1) !== 1) {
                RateLimiter::recordLoginAttempt($ip, false);
                Session::set('login_error', 'Account is disabled. Contact administrator.');
                Session::set('old_email', $email);
                Response::redirect('/admin/login');
            }

            // Success — clear rate limit, login, regenerate session, update last_login
            RateLimiter::recordLoginAttempt($ip, true);
            Auth::login($user);

            try {
                $this->userModel->updateLastLogin((int)$user['id']);
            } catch (\Exception $e) {
                // Log but don't fail login if last_login update fails
                error_log("Failed to update last_login: " . $e->getMessage());
            }

            // Flash success (optional)
            Session::set('flash_success', 'Welcome back, ' . ($user['name'] ?? $user['email']));

            Response::redirect('/admin/dashboard');

        } catch (\Exception $e) {
            error_log("Login error: " . $e->getMessage());
            Session::set('login_error', 'An error occurred. Please try again.');
            Session::set('old_email', $email);
            Response::redirect('/admin/login');
        }
    }

    // POST /admin/logout
    public function logout(): void
    {
        Session::start();

        if (!Request::isPost()) {
            Response::status(405, 'Method Not Allowed');
        }

        // CSRF protection for logout
        $csrf = (string) Request::post('_csrf', '');
        if (!Csrf::validate($csrf)) {
            // For logout, if CSRF fails, still destroy session for safety but log
            error_log("CSRF validation failed on logout, IP: " . Request::ip());
            // Still logout to be safe
        }

        Auth::logout();

        Response::redirect('/admin/login');
    }
}
