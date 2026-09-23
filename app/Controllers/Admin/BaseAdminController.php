<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Security;
use App\Core\View;

/**
 * Base Admin Controller — ensures admin auth
 * Milestone 4.1
 */
abstract class BaseAdminController
{
    protected View $view;

    public function __construct()
    {
        $this->view = new View(__DIR__ . '/../../Views');
        // Ensure admin auth for all child controllers except AuthController
        // AuthController will handle its own guest checks
        if (static::class !== AuthController::class) {
            Auth::requireAdmin();
        }
        Security::setSecureHeaders();
    }

    protected function render(string $template, array $data = []): string
    {
        return $this->view->render($template, $data);
    }

    protected function renderAdmin(string $template, array $data = []): void
    {
        // Wrap with admin layout
        $content = $this->render($template, $data);
        $layoutData = array_merge($data, ['content' => $content]);
        echo $this->render('admin/layout', $layoutData);
        exit;
    }
}
