<?php
/**
 * Admin Login — Black + Gold Professional
 * Milestone 4.1
 * 
 * Variables:
 * - $csrf_token
 * - $error
 * - $old_email
 */

use App\Core\Security;

$csrfToken = $csrf_token ?? '';
$error = $error ?? null;
$oldEmail = $old_email ?? '';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login — MTB Portfolio</title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-login-body">
<div class="admin-login-wrapper">
    <div class="admin-login-card">
        <div class="admin-login-header">
            <img src="/assets/img/brand/logo-lettermark-240.webp" width="80" height="54" alt="MTB Logo">
            <h1>Admin Login</h1>
            <p class="muted">Mashzidul Tanun Borshon — Portfolio CMS</p>
        </div>

        <?php if ($error): ?>
            <div class="flash flash--error" role="alert">
                <?= Security::e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/admin/login" class="admin-login-form" novalidate>
            <input type="hidden" name="_csrf" value="<?= Security::e($csrfToken) ?>">

            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autocomplete="email" autofocus
                       value="<?= Security::e($oldEmail) ?>" placeholder="admin@example.com">
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password"
                       placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn--primary btn--full">Login</button>

            <p class="admin-login-note">
                Secure admin area — authorized access only.<br>
                <a href="/" class="link-gold">← Back to Website</a>
            </p>
        </form>

        <div class="admin-login-footer">
            <span class="muted">M4.1 — PHP <?= PHP_VERSION ?> — No registration, no password reset yet</span>
        </div>
    </div>
</div>
</body>
</html>
