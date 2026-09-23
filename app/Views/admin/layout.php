<?php
/**
 * Admin Layout — Reusable
 * Milestone 4.1 — Black/dark base + gold accent, responsive, sidebar
 * 
 * Variables expected:
 * - $content (string) — main content HTML
 * - $title (string) — page title
 * - $page_title (string) — h1
 * - $current_route (string) — for active nav
 * - $user (array) — logged in user
 */

use App\Security\Csrf;

$currentRoute = $current_route ?? 'dashboard';
$user = $user ?? null;
$title = $title ?? 'Admin';
$pageTitle = $page_title ?? $title;
$content = $content ?? '<p>Coming in the next milestone.</p>';

// Flash messages
$flashSuccess = \App\Core\Session::get('flash_success');
$flashError = \App\Core\Session::get('flash_error');
\App\Core\Session::remove('flash_success');
\App\Core\Session::remove('flash_error');

$csrfToken = Csrf::getToken();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= \App\Core\Security::e($title) ?> — Admin — <?= \App\Core\Security::e(\App\Config\Config::get('site_name', 'MTB Portfolio')) ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-body">
<a class="skip-link" href="#admin-main">Skip to content</a>

<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="admin-sidebar" aria-label="Admin navigation">
        <div class="admin-sidebar__header">
            <a href="/admin/dashboard" class="admin-logo">
                <img src="/assets/img/brand/logo-lettermark-240.webp" width="48" height="32" alt="MTB">
                <span>MTB Admin</span>
            </a>
            <button class="admin-sidebar__close" id="admin-sidebar-close" aria-label="Close menu">✕</button>
        </div>

        <nav class="admin-nav" aria-label="Primary">
            <div class="admin-nav__section">
                <span class="admin-nav__label">Overview</span>
                <a href="/admin/dashboard" class="admin-nav__link <?= $currentRoute==='dashboard' ? 'is-active' : '' ?>">
                    <span class="ico">◧</span> Dashboard
                </a>
            </div>

            <div class="admin-nav__section">
                <span class="admin-nav__label">Content</span>
                <a href="/admin/pages" class="admin-nav__link <?= $currentRoute==='pages' ? 'is-active' : '' ?>"><span class="ico">📄</span> Pages</a>
                <a href="/admin/services" class="admin-nav__link <?= $currentRoute==='services' ? 'is-active' : '' ?>"><span class="ico">🛠️</span> Services</a>
                <a href="/admin/projects" class="admin-nav__link <?= $currentRoute==='projects' ? 'is-active' : '' ?>"><span class="ico">💼</span> Projects</a>
                <a href="/admin/blog" class="admin-nav__link <?= in_array($currentRoute, ['blog','blog_posts','blog_categories','blog_tags']) ? 'is-active' : '' ?>"><span class="ico">📝</span> Blog</a>
                <a href="/admin/testimonials" class="admin-nav__link <?= $currentRoute==='testimonials' ? 'is-active' : '' ?>"><span class="ico">⭐</span> Testimonials</a>
            </div>

            <div class="admin-nav__section">
                <span class="admin-nav__label">Website</span>
                <a href="/admin/settings" class="admin-nav__link <?= $currentRoute==='settings' ? 'is-active' : '' ?>"><span class="ico">⚙️</span> Site Settings</a>
                <a href="/admin/navigation" class="admin-nav__link <?= $currentRoute==='navigation' ? 'is-active' : '' ?>"><span class="ico">🧭</span> Navigation</a>
                <a href="/admin/seo" class="admin-nav__link <?= $currentRoute==='seo' ? 'is-active' : '' ?>"><span class="ico">🔍</span> SEO</a>
                <a href="/admin/media" class="admin-nav__link <?= $currentRoute==='media' ? 'is-active' : '' ?>"><span class="ico">🖼️</span> Media</a>
            </div>

            <div class="admin-nav__section">
                <span class="admin-nav__label">Communication</span>
                <a href="/admin/messages" class="admin-nav__link <?= $currentRoute==='messages' ? 'is-active' : '' ?>"><span class="ico">✉️</span> Messages</a>
            </div>

            <div class="admin-nav__section">
                <span class="admin-nav__label">System</span>
                <a href="/admin/account" class="admin-nav__link <?= $currentRoute==='account' ? 'is-active' : '' ?>"><span class="ico">👤</span> Admin Account</a>
                <form method="POST" action="/admin/logout" class="admin-nav__logout-form">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Security::e($csrfToken) ?>">
                    <button type="submit" class="admin-nav__link admin-nav__link--logout"><span class="ico">🚪</span> Logout</button>
                </form>
            </div>
        </nav>

        <div class="admin-sidebar__footer">
            <span class="muted">M4.1 — Admin Auth + Shell</span><br>
            <span class="muted" style="font-size:11px">© <?= date('Y') ?> MTB</span>
        </div>
    </aside>

    <!-- Main -->
    <div class="admin-main-wrapper">
        <header class="admin-header">
            <button class="admin-header__burger" id="admin-burger" aria-label="Open menu" aria-expanded="false" aria-controls="admin-sidebar">
                <span></span><span></span><span></span>
            </button>
            <div class="admin-header__title">
                <h1><?= \App\Core\Security::e($pageTitle) ?></h1>
                <nav class="admin-breadcrumb" aria-label="Breadcrumb">
                    <a href="/admin/dashboard">Dashboard</a>
                    <?php if ($currentRoute !== 'dashboard'): ?>
                        <span class="sep">/</span><span><?= \App\Core\Security::e($pageTitle) ?></span>
                    <?php endif; ?>
                </nav>
            </div>
            <div class="admin-header__actions">
                <?php if ($user): ?>
                    <span class="admin-user">
                        <span class="admin-user__name"><?= \App\Core\Security::e($user['name'] ?? $user['email'] ?? 'Admin') ?></span>
                        <span class="admin-user__role"><?= \App\Core\Security::e($user['role'] ?? 'admin') ?></span>
                    </span>
                <?php endif; ?>
                <a href="/" target="_blank" rel="noopener" class="btn btn--ghost btn--sm">View Site ↗</a>
            </div>
        </header>

        <main class="admin-main" id="admin-main">
            <?php if ($flashSuccess): ?>
                <div class="flash flash--success" role="status"><?= \App\Core\Security::e($flashSuccess) ?></div>
            <?php endif; ?>
            <?php if ($flashError): ?>
                <div class="flash flash--error" role="alert"><?= \App\Core\Security::e($flashError) ?></div>
            <?php endif; ?>

            <?= $content ?>
        </main>

        <footer class="admin-footer">
            <span>MTB Portfolio CMS — M4.1 Admin Auth + Dashboard Shell — PHP <?= PHP_VERSION ?> — <?= \App\Config\Config::env() ?></span>
        </footer>
    </div>
</div>

<script src="/assets/js/admin.js" defer></script>
</body>
</html>
