<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Service;
use App\Security\Csrf;
use App\Validation\Validator;

class ServicesController extends BaseAdminController
{
    private Service $serviceModel;

    public function __construct()
    {
        parent::__construct();
        $this->serviceModel = new Service();
    }

    public function index(): void
    {
        $services = $this->serviceModel->getAllForAdmin();
        $this->renderAdmin('admin/services/index', [
            'title' => 'Services',
            'page_title' => 'Services',
            'services' => $services,
            'current_route' => 'services',
            'user' => \App\Core\Auth::user(),
        ]);
    }

    public function create(): void
    {
        $this->renderAdmin('admin/services/form', [
            'title' => 'Create Service',
            'page_title' => 'Create Service',
            'service' => null,
            'is_edit' => false,
            'current_route' => 'services',
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
            Response::redirect('/admin/services/create');
        }

        $data = $this->sanitizeInput($_POST);
        $errors = $this->validate($data);

        if (empty($errors['slug']) && $this->serviceModel->slugExists($data['slug'])) {
            $errors['slug'] = 'Slug already exists.';
        }

        if (!empty($errors)) {
            Session::set('flash_error', 'Please fix validation errors.');
            $this->renderAdmin('admin/services/form', [
                'title' => 'Create Service',
                'page_title' => 'Create Service',
                'service' => $data,
                'errors' => $errors,
                'is_edit' => false,
                'current_route' => 'services',
                'user' => \App\Core\Auth::user(),
            ]);
            return;
        }

        try {
            $id = $this->serviceModel->create($data);
            Session::set('flash_success', 'Service created (ID: '.$id.').');
            Response::redirect('/admin/services');
        } catch (\Exception $e) {
            error_log("Service create error: ".$e->getMessage());
            Session::set('flash_error', 'Failed to create service: '.$e->getMessage());
            Response::redirect('/admin/services/create');
        }
    }

    public function edit(string $id): void
    {
        $idInt = $this->validateId($id);
        $service = $this->serviceModel->findByIdAdmin($idInt);
        if (!$service) {
            Session::set('flash_error', 'Service not found.');
            Response::redirect('/admin/services');
        }

        $this->renderAdmin('admin/services/form', [
            'title' => 'Edit Service',
            'page_title' => 'Edit Service: '.($service['title'] ?? $service['slug']),
            'service' => $service,
            'is_edit' => true,
            'current_route' => 'services',
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
            Response::redirect('/admin/services/'.intval($id).'/edit');
        }

        $idInt = $this->validateId($id);
        $existing = $this->serviceModel->findByIdAdmin($idInt);
        if (!$existing) {
            Session::set('flash_error', 'Service not found.');
            Response::redirect('/admin/services');
        }

        $data = $this->sanitizeInput($_POST);
        $errors = $this->validate($data);

        if (empty($errors['slug']) && $this->serviceModel->slugExists($data['slug'], $idInt)) {
            $errors['slug'] = 'Slug already exists.';
        }

        if (!empty($errors)) {
            Session::set('flash_error', 'Please fix validation errors.');
            $this->renderAdmin('admin/services/form', [
                'title' => 'Edit Service',
                'page_title' => 'Edit Service: '.($existing['title'] ?? $existing['slug']),
                'service' => array_merge($existing, $data, ['id'=>$idInt]),
                'errors' => $errors,
                'is_edit' => true,
                'current_route' => 'services',
                'user' => \App\Core\Auth::user(),
            ]);
            return;
        }

        try {
            $this->serviceModel->update($idInt, $data);
            Session::set('flash_success', 'Service updated.');
            Response::redirect('/admin/services');
        } catch (\Exception $e) {
            error_log("Service update error: ".$e->getMessage());
            Session::set('flash_error', 'Failed to update service: '.$e->getMessage());
            Response::redirect('/admin/services/'.$idInt.'/edit');
        }
    }

    public function delete(string $id): void
    {
        if (!Request::isPost()) {
            Response::methodNotAllowed();
        }

        if (!Csrf::validate(Request::post('_csrf'))) {
            Session::set('flash_error', 'Invalid CSRF token. Delete aborted.');
            Response::redirect('/admin/services');
        }

        $idInt = $this->validateId($id);
        $existing = $this->serviceModel->findByIdAdmin($idInt);
        if (!$existing) {
            Session::set('flash_error', 'Service not found.');
            Response::redirect('/admin/services');
        }

        try {
            $this->serviceModel->delete($idInt);
            Session::set('flash_success', 'Service deleted.');
            Response::redirect('/admin/services');
        } catch (\Exception $e) {
            error_log("Service delete error: ".$e->getMessage());
            Session::set('flash_error', 'Failed to delete service: '.$e->getMessage());
            Response::redirect('/admin/services');
        }
    }

    private function validateId(string $id): int
    {
        if (!ctype_digit($id) || (int)$id <=0) {
            Session::set('flash_error', 'Invalid ID.');
            Response::redirect('/admin/services');
        }
        return (int)$id;
    }

    private function sanitizeInput(array $post): array
    {
        return [
            'slug' => trim($post['slug'] ?? ''),
            'title' => trim($post['title'] ?? ''),
            'description' => trim($post['description'] ?? ''),
            'icon_key' => trim($post['icon_key'] ?? ''),
            'order_index' => isset($post['order_index']) && $post['order_index'] !== '' ? (int)$post['order_index'] : 0,
            'is_visible' => isset($post['is_visible']) ? (int)$post['is_visible'] : 1,
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if (!Validator::required($data['title']) || !Validator::length($data['title'], 2, 255)) {
            $errors['title'] = 'Title required (2-255 chars).';
        }

        if (!Validator::required($data['slug'])) {
            $errors['slug'] = 'Slug required.';
        } elseif (!Validator::slug($data['slug'])) {
            $errors['slug'] = 'Slug must be ^[a-z0-9-]+$';
        } elseif (!Validator::length($data['slug'], 2, 255)) {
            $errors['slug'] = 'Slug 2-255 chars.';
        }

        if (!empty($data['description']) && !Validator::maxLength($data['description'], 5000)) {
            $errors['description'] = 'Description max 5000 chars.';
        }

        if (!empty($data['icon_key']) && !Validator::maxLength($data['icon_key'], 50)) {
            $errors['icon_key'] = 'Icon key max 50 chars.';
        } elseif (!empty($data['icon_key']) && !preg_match('/^[a-z0-9_-]+$/', $data['icon_key'])) {
            $errors['icon_key'] = 'Icon key must be [a-z0-9_-]';
        }

        if (!is_int($data['order_index']) || $data['order_index'] <0 || $data['order_index'] > 9999) {
            $errors['order_index'] = 'Order must be 0-9999.';
        }

        return $errors;
    }
}
