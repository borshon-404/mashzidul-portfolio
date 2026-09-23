<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Config\Config;

/**
 * Admin Dashboard Controller — Shell/UI Only
 * Milestone 4.1 — No CRUD yet
 */
class DashboardController extends BaseAdminController
{
    // GET /admin/ → redirect to /admin/dashboard
    public function index(): void
    {
        Response::redirect('/admin/dashboard');
    }

    // GET /admin/dashboard
    public function dashboard(): void
    {
        // Real DB counts where safely available
        $counts = $this->getCounts();
        $systemStatus = $this->getSystemStatus();

        $user = \App\Core\Auth::user();

        $this->renderAdmin('admin/dashboard', [
            'title' => 'Dashboard',
            'page_title' => 'Dashboard',
            'counts' => $counts,
            'system_status' => $systemStatus,
            'user' => $user,
            'current_route' => 'dashboard',
        ]);
    }

    private function getCounts(): array
    {
        $counts = [
            'pages' => 0,
            'services' => 0,
            'projects' => 0,
            'blog_published' => 0,
            'blog_draft' => 0,
            'blog_total' => 0,
            'testimonials' => 0,
            'messages' => 0,
            'messages_unread' => 0,
            'media' => 0,
            'users' => 0,
        ];

        try {
            $db = Database::getInstance();

            // Pages
            $counts['pages'] = (int) $db->query("SELECT COUNT(*) FROM pages")->fetchColumn();

            // Services
            $counts['services'] = (int) $db->query("SELECT COUNT(*) FROM services")->fetchColumn();

            // Projects
            $counts['projects'] = (int) $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();

            // Blog
            $counts['blog_published'] = (int) $db->query("SELECT COUNT(*) FROM blog_posts WHERE status='published'")->fetchColumn();
            $counts['blog_draft'] = (int) $db->query("SELECT COUNT(*) FROM blog_posts WHERE status='draft'")->fetchColumn();
            $counts['blog_total'] = $counts['blog_published'] + $counts['blog_draft'];

            // Testimonials
            $counts['testimonials'] = (int) $db->query("SELECT COUNT(*) FROM testimonials")->fetchColumn();

            // Messages
            $counts['messages'] = (int) $db->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
            $counts['messages_unread'] = (int) $db->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn();

            // Media
            $counts['media'] = (int) $db->query("SELECT COUNT(*) FROM media")->fetchColumn();

            // Users
            $counts['users'] = (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn();

        } catch (\Exception $e) {
            // If DB not available or tables not yet migrated, keep 0 and log
            error_log("Dashboard counts error: " . $e->getMessage());
        }

        return $counts;
    }

    private function getSystemStatus(): array
    {
        $status = [
            'db_connected' => false,
            'db_version' => null,
            'db_tables' => 0,
            'php_version' => PHP_VERSION,
            'env' => Config::env(),
            'app_version' => 'M4.1',
            'milestone' => 'Admin Auth + Dashboard Shell',
        ];

        try {
            $test = Database::testConnection();
            $status['db_connected'] = $test['connected'] ?? false;
            $status['db_version'] = $test['version'] ?? null;
            $status['db_tables'] = count($test['tables'] ?? []);
            $status['db_error'] = $test['error'] ?? null;
        } catch (\Exception $e) {
            $status['db_connected'] = false;
            $status['db_error'] = $e->getMessage();
        }

        return $status;
    }

    // Placeholder for future sections — all show "Coming in next milestone"
    public function placeholder(string $section): void
    {
        $titles = [
            'pages' => 'Pages',
            'services' => 'Services',
            'projects' => 'Projects',
            'blog' => 'Blog',
            'testimonials' => 'Testimonials',
            'settings' => 'Site Settings',
            'navigation' => 'Navigation',
            'seo' => 'SEO',
            'media' => 'Media Library',
            'messages' => 'Messages',
            'account' => 'Admin Account',
        ];

        $title = $titles[$section] ?? ucfirst($section);

        $this->renderAdmin('admin/placeholder', [
            'title' => $title,
            'page_title' => $title,
            'section' => $section,
            'current_route' => $section,
        ]);
    }
}
