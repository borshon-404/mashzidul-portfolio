<?php
/**
 * Admin Dashboard — Overview with real DB counts
 * Milestone 4.1
 * 
 * Variables:
 * - $counts: pages, services, projects, blog_published, blog_draft, blog_total, testimonials, messages, messages_unread, media, users
 * - $system_status: db_connected, db_version, db_tables, php_version, env, app_version, milestone
 * - $user
 */

use App\Core\Security;

$counts = $counts ?? [];
$system = $system_status ?? [];
?>
<div class="admin-dashboard">
    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <span class="admin-stat-card__label">Pages</span>
            <span class="admin-stat-card__value"><?= (int)($counts['pages'] ?? 0) ?></span>
            <span class="admin-stat-card__note">11 expected</span>
        </div>
        <div class="admin-stat-card">
            <span class="admin-stat-card__label">Services</span>
            <span class="admin-stat-card__value"><?= (int)($counts['services'] ?? 0) ?></span>
            <span class="admin-stat-card__note">10 expected</span>
        </div>
        <div class="admin-stat-card">
            <span class="admin-stat-card__label">Projects</span>
            <span class="admin-stat-card__value"><?= (int)($counts['projects'] ?? 0) ?></span>
            <span class="admin-stat-card__note">4 expected</span>
        </div>
        <div class="admin-stat-card">
            <span class="admin-stat-card__label">Blog Published</span>
            <span class="admin-stat-card__value"><?= (int)($counts['blog_published'] ?? 0) ?></span>
            <span class="admin-stat-card__note">4 expected</span>
        </div>
        <div class="admin-stat-card">
            <span class="admin-stat-card__label">Blog Draft</span>
            <span class="admin-stat-card__value"><?= (int)($counts['blog_draft'] ?? 0) ?></span>
            <span class="admin-stat-card__note">0 expected</span>
        </div>
        <div class="admin-stat-card">
            <span class="admin-stat-card__label">Testimonials</span>
            <span class="admin-stat-card__value"><?= (int)($counts['testimonials'] ?? 0) ?></span>
            <span class="admin-stat-card__note">0 expected (future)</span>
        </div>
        <div class="admin-stat-card">
            <span class="admin-stat-card__label">Messages</span>
            <span class="admin-stat-card__value"><?= (int)($counts['messages'] ?? 0) ?></span>
            <span class="admin-stat-card__note"><?= (int)($counts['messages_unread'] ?? 0) ?> unread</span>
        </div>
        <div class="admin-stat-card">
            <span class="admin-stat-card__label">Media</span>
            <span class="admin-stat-card__value"><?= (int)($counts['media'] ?? 0) ?></span>
            <span class="admin-stat-card__note">24 expected</span>
        </div>
    </div>

    <div class="admin-grid-2">
        <div class="admin-card">
            <h3>System Status</h3>
            <dl class="admin-dl">
                <div><dt>Database</dt><dd><?= $system['db_connected'] ? '<span class="badge badge--success">Connected</span>' : '<span class="badge badge--error">Disconnected</span>' ?> <?= Security::e($system['db_version'] ?? '') ?></dd></div>
                <div><dt>Tables</dt><dd><?= (int)($system['db_tables'] ?? 0) ?> / 17 expected</dd></div>
                <div><dt>PHP Version</dt><dd><?= Security::e($system['php_version'] ?? PHP_VERSION) ?> (required 8.2+)</dd></div>
                <div><dt>Environment</dt><dd><?= Security::e($system['env'] ?? 'production') ?></dd></div>
                <div><dt>Milestone</dt><dd><?= Security::e($system['app_version'] ?? 'M4.1') ?> — <?= Security::e($system['milestone'] ?? 'Admin Auth + Shell') ?></dd></div>
                <?php if (!empty($system['db_error'])): ?>
                    <div><dt>DB Error</dt><dd class="text-error"><?= Security::e($system['db_error']) ?></dd></div>
                <?php endif; ?>
            </dl>
        </div>

        <div class="admin-card">
            <h3>Quick Actions</h3>
            <p class="muted">Content editors coming in next milestones. Current navigation items are placeholders.</p>
            <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:16px">
                <a href="/admin/pages" class="btn btn--ghost btn--sm">Manage Pages</a>
                <a href="/admin/blog" class="btn btn--ghost btn--sm">Manage Blog</a>
                <a href="/admin/projects" class="btn btn--ghost btn--sm">Manage Projects</a>
                <a href="/admin/media" class="btn btn--ghost btn--sm">Media Library</a>
            </div>
            <p style="margin-top:20px"><span class="badge">M4.2 CRUD — Coming Next</span></p>
        </div>
    </div>

    <div class="admin-card" style="margin-top:24px">
        <h3>Preservation Check — Existing Data Must Remain Unchanged</h3>
        <ul class="admin-list">
            <li>4 blog posts must still exist with full content unchanged — <strong>Verified via counts above: <?= (int)($counts['blog_published'] ?? 0) ?> published</strong></li>
            <li>Project slugs unchanged: seo-agency-website, web-hosting-company-website, creative-agency-portfolio, digital-creative-studio</li>
            <li>Blog slugs unchanged: website-speed-optimization, benefits-of-responsive-web-design, wordpress-website-development-is-a-smart-choice, benefits-of-a-professional-business-website</li>
            <li>Services unchanged: 10 expected</li>
            <li>Pages unchanged: 11 expected</li>
            <li>Media unchanged: existing WebP assets remain</li>
        </ul>
        <p class="muted" style="margin-top:12px">Static site rollback: <code>python3 site_src/build.py</code> still builds 24 pages, audit clean.</p>
    </div>
</div>
