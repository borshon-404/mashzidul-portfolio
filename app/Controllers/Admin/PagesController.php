<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Security;
use App\Core\Session;
use App\Models\Page;
use App\Security\Csrf;
use App\Validation\Validator;

/**
 * Pages CRUD — M4.2
 */
class PagesController extends BaseAdminController
{
    private Page $pageModel;

    public function __construct()
    {
        parent::__construct();
        $this->pageModel = new Page();
    }

    // GET /admin/pages
    public function index(): void
    {
        $pages = $this->pageModel->getAllForAdmin();
        $this->renderAdmin('admin/pages/index', [
            'title' => 'Pages',
            'page_title' => 'Pages',
            'pages' => $pages,
            'current_route' => 'pages',
            'user' => \App\Core\Auth::user(),
        ]);
    }

    // GET /admin/pages/create
    public function create(): void
    {
        $this->renderAdmin('admin/pages/form', [
            'title' => 'Create Page',
            'page_title' => 'Create Page',
            'page' => null,
            'is_edit' => false,
            'current_route' => 'pages',
            'user' => \App\Core\Auth::user(),
        ]);
    }

    // POST /admin/pages
    public function store(): void
    {
        if (!Request::isPost()) {
            Response::methodNotAllowed();
        }

        $csrf = Request::post('_csrf');
        if (!Csrf::validate($csrf)) {
            Session::set('flash_error', 'Invalid CSRF token. Please try again.');
            Response::redirect('/admin/pages/create');
        }

        $data = $this->sanitizeInput($_POST);
        $errors = $this->validate($data);

        // Check slug duplicate
        if (empty($errors['slug']) && $this->pageModel->slugExists($data['slug'])) {
            $errors['slug'] = 'Slug already exists. Choose a unique slug.';
        }

        if (!empty($errors)) {
            Session::set('flash_error', 'Please fix validation errors.');
            // Render form with errors and old data
            $this->renderAdmin('admin/pages/form', [
                'title' => 'Create Page',
                'page_title' => 'Create Page',
                'page' => $data,
                'errors' => $errors,
                'is_edit' => false,
                'current_route' => 'pages',
                'user' => \App\Core\Auth::user(),
            ]);
            return;
        }

        try {
            $id = $this->pageModel->create($data);
            Session::set('flash_success', 'Page created successfully (ID: '.$id.').');
            Response::redirect('/admin/pages');
        } catch (\Exception $e) {
            error_log("Page create error: " . $e->getMessage());
            Session::set('flash_error', 'Failed to create page: ' . $e->getMessage());
            Response::redirect('/admin/pages/create');
        }
    }

    // GET /admin/pages/{id}/edit
    public function edit(string $id): void
    {
        $idInt = $this->validateId($id);
        $page = $this->pageModel->findByIdAdmin($idInt);
        if (!$page) {
            Session::set('flash_error', 'Page not found.');
            Response::redirect('/admin/pages');
        }

        $this->renderAdmin('admin/pages/form', [
            'title' => 'Edit Page',
            'page_title' => 'Edit Page: ' . ($page['title'] ?? $page['slug']),
            'page' => $page,
            'is_edit' => true,
            'current_route' => 'pages',
            'user' => \App\Core\Auth::user(),
        ]);
    }

    // POST /admin/pages/{id}/update
    public function update(string $id): void
    {
        if (!Request::isPost()) {
            Response::methodNotAllowed();
        }

        $csrf = Request::post('_csrf');
        if (!Csrf::validate($csrf)) {
            Session::set('flash_error', 'Invalid CSRF token.');
            Response::redirect('/admin/pages/' . intval($id) . '/edit');
        }

        $idInt = $this->validateId($id);
        $existing = $this->pageModel->findByIdAdmin($idInt);
        if (!$existing) {
            Session::set('flash_error', 'Page not found.');
            Response::redirect('/admin/pages');
        }

        $data = $this->sanitizeInput($_POST);
        $errors = $this->validate($data);

        if (empty($errors['slug']) && $this->pageModel->slugExists($data['slug'], $idInt)) {
            $errors['slug'] = 'Slug already exists. Choose a unique slug.';
        }

        if (!empty($errors)) {
            Session::set('flash_error', 'Please fix validation errors.');
            $this->renderAdmin('admin/pages/form', [
                'title' => 'Edit Page',
                'page_title' => 'Edit Page: ' . ($existing['title'] ?? $existing['slug']),
                'page' => array_merge($existing, $data, ['id'=>$idInt]),
                'errors' => $errors,
                'is_edit' => true,
                'current_route' => 'pages',
                'user' => \App\Core\Auth::user(),
            ]);
            return;
        }

        try {
            $this->pageModel->update($idInt, $data);
            Session::set('flash_success', 'Page updated successfully.');
            Response::redirect('/admin/pages');
        } catch (\Exception $e) {
            error_log("Page update error: " . $e->getMessage());
            Session::set('flash_error', 'Failed to update page: ' . $e->getMessage());
            Response::redirect('/admin/pages/' . $idInt . '/edit');
        }
    }

    // POST /admin/pages/{id}/delete
    public function delete(string $id): void
    {
        if (!Request::isPost()) {
            Response::methodNotAllowed();
        }

        $csrf = Request::post('_csrf');
        if (!Csrf::validate($csrf)) {
            Session::set('flash_error', 'Invalid CSRF token. Delete aborted.');
            Response::redirect('/admin/pages');
        }

        $idInt = $this->validateId($id);
        $existing = $this->pageModel->findByIdAdmin($idInt);
        if (!$existing) {
            Session::set('flash_error', 'Page not found.');
            Response::redirect('/admin/pages');
        }

        // Check for page_sections that will be cascade deleted — allow but warn
        $sectionCount = $this->pageModel->countSections($idInt);
        if ($sectionCount > 0) {
            // We allow deletion, CASCADE will remove sections, but we log
            error_log("Deleting page ID $idInt with $sectionCount sections (CASCADE)");
        }

        try {
            $this->pageModel->delete($idInt);
            Session::set('flash_success', 'Page deleted successfully. ' . ($sectionCount ? "($sectionCount sections removed via CASCADE)" : ''));
            Response::redirect('/admin/pages');
        } catch (\Exception $e) {
            error_log("Page delete error: " . $e->getMessage());
            Session::set('flash_error', 'Failed to delete page: ' . $e->getMessage());
            Response::redirect('/admin/pages');
        }
    }

    private function validateId(string $id): int
    {
        if (!ctype_digit($id) || (int)$id <= 0) {
            Session::set('flash_error', 'Invalid ID.');
            Response::redirect('/admin/pages');
        }
        return (int) $id;
    }

    private function sanitizeInput(array $post): array
    {
        return [
            'slug' => trim($post['slug'] ?? ''),
            'title' => trim($post['title'] ?? ''),
            'meta_title' => trim($post['meta_title'] ?? ''),
            'meta_description' => trim($post['meta_description'] ?? ''),
            'canonical_url' => trim($post['canonical_url'] ?? ''),
            'og_title' => trim($post['og_title'] ?? ''),
            'og_description' => trim($post['og_description'] ?? ''),
            'og_image_id' => !empty($post['og_image_id']) ? (int) $post['og_image_id'] : null,
            'twitter_title' => trim($post['twitter_title'] ?? ''),
            'twitter_description' => trim($post['twitter_description'] ?? ''),
            'twitter_image_id' => !empty($post['twitter_image_id']) ? (int) $post['twitter_image_id'] : null,
            'robots' => trim($post['robots'] ?? 'index, follow'),
            'is_visible' => isset($post['is_visible']) ? (int) $post['is_visible'] : 1,
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if (!Validator::required($data['title']) || !Validator::length($data['title'], 2, 255)) {
            $errors['title'] = 'Title is required (2-255 chars).';
        }

        if (!Validator::required($data['slug'])) {
            $errors['slug'] = 'Slug is required.';
        } elseif (!Validator::slug($data['slug'])) {
            $errors['slug'] = 'Slug must match ^[a-z0-9-]+$ (lowercase, numbers, hyphens).';
        } elseif (!Validator::length($data['slug'], 2, 255)) {
            $errors['slug'] = 'Slug must be 2-255 chars.';
        }

        if (!empty($data['meta_title']) && !Validator::maxLength($data['meta_title'], 255)) {
            $errors['meta_title'] = 'Meta title max 255 chars.';
        }

        if (!empty($data['meta_description']) && !Validator::maxLength($data['meta_description'], 1000)) {
            $errors['meta_description'] = 'Meta description max 1000 chars.';
        }

        if (!empty($data['canonical_url']) && !Validator::url($data['canonical_url'])) {
            $errors['canonical_url'] = 'Canonical URL must be a valid URL.';
        } elseif (!empty($data['canonical_url']) && !Validator::maxLength($data['canonical_url'], 255)) {
            $errors['canonical_url'] = 'Canonical URL max 255 chars.';
        }

        if (!empty($data['og_title']) && !Validator::maxLength($data['og_title'], 255)) {
            $errors['og_title'] = 'OG title max 255 chars.';
        }

        if (!empty($data['og_description']) && !Validator::maxLength($data['og_description'], 1000)) {
            $errors['og_description'] = 'OG description max 1000 chars.';
        }

        if (!empty($data['twitter_title']) && !Validator::maxLength($data['twitter_title'], 255)) {
            $errors['twitter_title'] = 'Twitter title max 255 chars.';
        }

        if (!empty($data['twitter_description']) && !Validator::maxLength($data['twitter_description'], 1000)) {
            $errors['twitter_description'] = 'Twitter description max 1000 chars.';
        }

        if (!empty($data['robots']) && !Validator::maxLength($data['robots'], 100)) {
            $errors['robots'] = 'Robots max 100 chars.';
        }

        $allowedRobots = ['index, follow', 'noindex, nofollow', 'index, nofollow', 'noindex, follow'];
        if (!empty($data['robots']) && !Validator::inArray($data['robots'], $allowedRobots)) {
            // Allow custom but warn if not in list — for strictness we allow any up to 100 chars, but check if contains only allowed words
            // For M4.2 we allow any, but if strict we could error. We'll allow.
        }

        if (!Validator::inArray($data['is_visible'], [0,1,'0','1'], )) {
            // Coerce
            $data['is_visible'] = $data['is_visible'] ? 1 : 0;
        }

        if (!empty($data['og_image_id']) && !is_int($data['og_image_id'])) {
            $errors['og_image_id'] = 'OG image ID must be integer.';
        }

        if (!empty($data['twitter_image_id']) && !is_int($data['twitter_image_id'])) {
            $errors['twitter_image_id'] = 'Twitter image ID must be integer.';
        }

        return $errors;
    }
}
