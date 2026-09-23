<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Project;
use App\Security\Csrf;
use App\Validation\Validator;

class ProjectsController extends BaseAdminController
{
    private Project $projectModel;

    public function __construct()
    {
        parent::__construct();
        $this->projectModel = new Project();
    }

    public function index(): void
    {
        $projects = $this->projectModel->getAllForAdmin();
        // Attach counts
        foreach ($projects as &$p) {
            $counts = $this->projectModel->countRelations((int)$p['id']);
            $p['_tech_count'] = $counts['technologies'];
            $p['_feat_count'] = $counts['features'];
        }
        unset($p);

        $this->renderAdmin('admin/projects/index', [
            'title' => 'Projects',
            'page_title' => 'Projects',
            'projects' => $projects,
            'current_route' => 'projects',
            'user' => \App\Core\Auth::user(),
        ]);
    }

    public function create(): void
    {
        $this->renderAdmin('admin/projects/form', [
            'title' => 'Create Project',
            'page_title' => 'Create Project',
            'project' => null,
            'is_edit' => false,
            'current_route' => 'projects',
            'user' => \App\Core\Auth::user(),
        ]);
    }

    public function store(): void
    {
        if (!Request::isPost()) {
            Response::methodNotAllowed();
        }

        if (!Csrf::validate(Request::post('_csrf'))) {
            Session::set('flash_error', 'Invalid CSRF token.');
            Response::redirect('/admin/projects/create');
        }

        $data = $this->sanitizeInput($_POST);
        $techs = $this->sanitizeTechnologies($_POST);
        $feats = $this->sanitizeFeatures($_POST);

        $errors = $this->validate($data, $techs, $feats);

        if (empty($errors['slug']) && $this->projectModel->slugExists($data['slug'])) {
            $errors['slug'] = 'Slug already exists.';
        }

        if (!empty($errors)) {
            Session::set('flash_error', 'Please fix validation errors.');
            $this->renderAdmin('admin/projects/form', [
                'title' => 'Create Project',
                'page_title' => 'Create Project',
                'project' => array_merge($data, ['technologies' => $techs, 'features_raw' => $feats]),
                'errors' => $errors,
                'is_edit' => false,
                'current_route' => 'projects',
                'user' => \App\Core\Auth::user(),
            ]);
            return;
        }

        try {
            $id = $this->projectModel->createWithRelations($data, $techs, $feats);
            Session::set('flash_success', 'Project created (ID: '.$id.').');
            Response::redirect('/admin/projects');
        } catch (\Exception $e) {
            error_log("Project create error: ".$e->getMessage());
            Session::set('flash_error', 'Failed to create project: '.$e->getMessage());
            Response::redirect('/admin/projects/create');
        }
    }

    public function edit(string $id): void
    {
        $idInt = $this->validateId($id);
        $project = $this->projectModel->findByIdAdmin($idInt);
        if (!$project) {
            Session::set('flash_error', 'Project not found.');
            Response::redirect('/admin/projects');
        }

        // Normalize for form: technologies as array of strings, features as array
        $techStrings = array_map(fn($t) => $t['technology'] ?? $t, $project['technologies'] ?? []);
        $featStrings = array_map(fn($f) => $f['feature'] ?? $f, $project['features'] ?? []);

        $project['technologies_list'] = $techStrings;
        $project['features_list'] = $featStrings;

        $this->renderAdmin('admin/projects/form', [
            'title' => 'Edit Project',
            'page_title' => 'Edit Project: '.($project['title'] ?? $project['slug']),
            'project' => $project,
            'is_edit' => true,
            'current_route' => 'projects',
            'user' => \App\Core\Auth::user(),
        ]);
    }

    public function update(string $id): void
    {
        if (!Request::isPost()) {
            Response::methodNotAllowed();
        }

        if (!Csrf::validate(Request::post('_csrf'))) {
            Session::set('flash_error', 'Invalid CSRF token.');
            Response::redirect('/admin/projects/'.intval($id).'/edit');
        }

        $idInt = $this->validateId($id);
        $existing = $this->projectModel->findByIdAdmin($idInt);
        if (!$existing) {
            Session::set('flash_error', 'Project not found.');
            Response::redirect('/admin/projects');
        }

        $data = $this->sanitizeInput($_POST);
        $techs = $this->sanitizeTechnologies($_POST);
        $feats = $this->sanitizeFeatures($_POST);

        $errors = $this->validate($data, $techs, $feats);

        if (empty($errors['slug']) && $this->projectModel->slugExists($data['slug'], $idInt)) {
            $errors['slug'] = 'Slug already exists.';
        }

        if (!empty($errors)) {
            Session::set('flash_error', 'Please fix validation errors.');
            $this->renderAdmin('admin/projects/form', [
                'title' => 'Edit Project',
                'page_title' => 'Edit Project: '.($existing['title'] ?? $existing['slug']),
                'project' => array_merge($existing, $data, ['technologies_list'=>$techs, 'features_list'=>$feats, 'id'=>$idInt]),
                'errors' => $errors,
                'is_edit' => true,
                'current_route' => 'projects',
                'user' => \App\Core\Auth::user(),
            ]);
            return;
        }

        try {
            $this->projectModel->updateWithRelations($idInt, $data, $techs, $feats);
            Session::set('flash_success', 'Project updated successfully.');
            Response::redirect('/admin/projects');
        } catch (\Exception $e) {
            error_log("Project update error: ".$e->getMessage());
            Session::set('flash_error', 'Failed to update project: '.$e->getMessage());
            Response::redirect('/admin/projects/'.$idInt.'/edit');
        }
    }

    public function delete(string $id): void
    {
        if (!Request::isPost()) {
            Response::methodNotAllowed();
        }

        if (!Csrf::validate(Request::post('_csrf'))) {
            Session::set('flash_error', 'Invalid CSRF token. Delete aborted.');
            Response::redirect('/admin/projects');
        }

        $idInt = $this->validateId($id);
        $existing = $this->projectModel->findByIdAdmin($idInt);
        if (!$existing) {
            Session::set('flash_error', 'Project not found.');
            Response::redirect('/admin/projects');
        }

        $counts = $this->projectModel->countRelations($idInt);

        try {
            $this->projectModel->delete($idInt);
            Session::set('flash_success', 'Project deleted. ('.$counts['technologies'].' technologies, '.$counts['features'].' features removed via CASCADE)');
            Response::redirect('/admin/projects');
        } catch (\Exception $e) {
            error_log("Project delete error: ".$e->getMessage());
            Session::set('flash_error', 'Failed to delete project: '.$e->getMessage());
            Response::redirect('/admin/projects');
        }
    }

    private function validateId(string $id): int
    {
        if (!ctype_digit($id) || (int)$id <=0) {
            Session::set('flash_error', 'Invalid ID.');
            Response::redirect('/admin/projects');
        }
        return (int)$id;
    }

    private function sanitizeInput(array $post): array
    {
        return [
            'slug' => trim($post['slug'] ?? ''),
            'title' => trim($post['title'] ?? ''),
            'category' => trim($post['category'] ?? ''),
            'duration' => trim($post['duration'] ?? ''),
            'cost' => trim($post['cost'] ?? ''),
            'role' => trim($post['role'] ?? ''),
            'overview' => trim($post['overview'] ?? ''),
            'live_url' => trim($post['live_url'] ?? ''),
            'github_url' => trim($post['github_url'] ?? ''),
            'client_name' => trim($post['client_name'] ?? ''),
            'project_date' => trim($post['project_date'] ?? ''),
            'art_media_id' => !empty($post['art_media_id']) ? (int)$post['art_media_id'] : null,
            'is_featured' => isset($post['is_featured']) ? 1 : 0,
            'is_visible' => isset($post['is_visible']) ? 1 : 0,
            'order_index' => isset($post['order_index']) && $post['order_index'] !== '' ? (int)$post['order_index'] : 0,
        ];
    }

    private function sanitizeTechnologies(array $post): array
    {
        $techs = [];
        // Support both array input technologies[] and textarea technologies_text
        if (isset($post['technologies']) && is_array($post['technologies'])) {
            foreach ($post['technologies'] as $t) {
                $t = trim((string)$t);
                if ($t !== '') $techs[] = $t;
            }
        } elseif (!empty($post['technologies_text'])) {
            // newline or comma separated
            $raw = $post['technologies_text'];
            $parts = preg_split('/[\r\n,]+/', $raw);
            foreach ($parts as $p) {
                $p = trim($p);
                if ($p !== '') $techs[] = $p;
            }
        }

        // Deduplicate case-insensitive but preserve original case, keep first occurrence
        $seen = [];
        $unique = [];
        foreach ($techs as $t) {
            $lower = mb_strtolower($t);
            if (!isset($seen[$lower])) {
                $seen[$lower] = true;
                $unique[] = $t;
            }
        }
        return $unique;
    }

    private function sanitizeFeatures(array $post): array
    {
        $feats = [];
        if (isset($post['features']) && is_array($post['features'])) {
            foreach ($post['features'] as $f) {
                $f = trim((string)$f);
                if ($f !== '') $feats[] = $f;
            }
        } elseif (!empty($post['features_text'])) {
            $raw = $post['features_text'];
            $parts = preg_split('/\r\n|\n|\r/', $raw);
            foreach ($parts as $p) {
                $p = trim($p);
                if ($p !== '') $feats[] = $p;
            }
        }
        return $feats;
    }

    private function validate(array $data, array $techs, array $feats): array
    {
        $errors = [];

        if (!Validator::required($data['title']) || !Validator::length($data['title'], 2, 255)) {
            $errors['title'] = 'Title required (2-255).';
        }

        if (!Validator::required($data['slug'])) {
            $errors['slug'] = 'Slug required.';
        } elseif (!Validator::slug($data['slug'])) {
            $errors['slug'] = 'Slug must be ^[a-z0-9-]+$';
        } elseif (!Validator::length($data['slug'], 2, 255)) {
            $errors['slug'] = 'Slug 2-255 chars.';
        }

        if (!empty($data['category']) && !Validator::maxLength($data['category'], 100)) {
            $errors['category'] = 'Category max 100.';
        }

        if (!empty($data['duration']) && !Validator::maxLength($data['duration'], 50)) {
            $errors['duration'] = 'Duration max 50.';
        }

        if (!empty($data['cost']) && !Validator::maxLength($data['cost'], 50)) {
            $errors['cost'] = 'Cost max 50.';
        }

        if (!empty($data['role']) && !Validator::maxLength($data['role'], 255)) {
            $errors['role'] = 'Role max 255.';
        }

        if (!empty($data['overview']) && !Validator::maxLength($data['overview'], 10000)) {
            $errors['overview'] = 'Overview max 10000 chars.';
        }

        if (!empty($data['live_url']) && !Validator::url($data['live_url'])) {
            $errors['live_url'] = 'Live URL must be valid URL.';
        } elseif (!empty($data['live_url']) && !Validator::maxLength($data['live_url'], 255)) {
            $errors['live_url'] = 'Live URL max 255.';
        }

        if (!empty($data['github_url']) && !Validator::url($data['github_url'])) {
            $errors['github_url'] = 'GitHub URL must be valid URL.';
        } elseif (!empty($data['github_url']) && !Validator::maxLength($data['github_url'], 255)) {
            $errors['github_url'] = 'GitHub URL max 255.';
        }

        if (!empty($data['client_name']) && !Validator::maxLength($data['client_name'], 255)) {
            $errors['client_name'] = 'Client name max 255.';
        }

        if (!empty($data['project_date'])) {
            $d = \DateTime::createFromFormat('Y-m-d', $data['project_date']);
            if (!$d || $d->format('Y-m-d') !== $data['project_date']) {
                $errors['project_date'] = 'Project date must be YYYY-MM-DD.';
            }
        }

        if ($data['order_index'] <0 || $data['order_index'] >9999) {
            $errors['order_index'] = 'Order 0-9999.';
        }

        // Technologies validation
        foreach ($techs as $idx => $t) {
            if (!Validator::length($t, 1, 100)) {
                $errors['technologies'] = 'Each technology 1-100 chars. Invalid at #'.($idx+1);
                break;
            }
        }

        if (count($techs) > 50) {
            $errors['technologies'] = 'Max 50 technologies.';
        }

        // Features validation
        foreach ($feats as $idx => $f) {
            if (!Validator::length($f, 1, 2000)) {
                $errors['features'] = 'Each feature 1-2000 chars. Invalid at #'.($idx+1);
                break;
            }
        }

        if (count($feats) > 100) {
            $errors['features'] = 'Max 100 features.';
        }

        return $errors;
    }
}
