<?php
declare(strict_types=1);

/**
 * MTB Portfolio — Front Controller
 * Milestone 4.2: Core Content CRUD (Pages, Services, Projects)
 * 
 * Static site (index.html) remains functional because .htaccess routes only
 * non-existing files/dirs to this front controller.
 */

require_once __DIR__ . '/../app/Config/Config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Request.php';
require_once __DIR__ . '/../app/Core/Response.php';
require_once __DIR__ . '/../app/Core/Router.php';
require_once __DIR__ . '/../app/Core/Session.php';
require_once __DIR__ . '/../app/Core/Security.php';
require_once __DIR__ . '/../app/Core/Auth.php';
require_once __DIR__ . '/../app/Core/View.php';

use App\Config\Config;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Security;
use App\Core\View;

// Load config
try {
    Config::load();
} catch (Exception $e) {
    if (getenv('APP_ENV') === 'development') {
        die("Config error: " . $e->getMessage());
    }
    http_response_code(500);
    die("Configuration error. Check config.php");
}

// Secure headers
Security::setSecureHeaders();

// Session start
\App\Core\Session::start();

// ---------------------------------------------------------------------------
// Load Models, Security, Validation, Admin Controllers (M4.2)
// ---------------------------------------------------------------------------
require_once __DIR__ . '/../app/Models/BaseModel.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Models/SiteSettings.php';
require_once __DIR__ . '/../app/Models/Navigation.php';
require_once __DIR__ . '/../app/Models/Page.php';
require_once __DIR__ . '/../app/Models/PageSection.php';
require_once __DIR__ . '/../app/Models/Service.php';
require_once __DIR__ . '/../app/Models/Project.php';
require_once __DIR__ . '/../app/Models/ProjectTechnology.php';
require_once __DIR__ . '/../app/Models/ProjectFeature.php';
require_once __DIR__ . '/../app/Models/BlogCategory.php';
require_once __DIR__ . '/../app/Models/BlogTag.php';
require_once __DIR__ . '/../app/Models/BlogPost.php';
require_once __DIR__ . '/../app/Models/Media.php';
require_once __DIR__ . '/../app/Models/ContactMessage.php';
require_once __DIR__ . '/../app/Models/Testimonial.php';
require_once __DIR__ . '/../app/Models/SeoSetting.php';
require_once __DIR__ . '/../app/Security/Csrf.php';
require_once __DIR__ . '/../app/Security/RateLimiter.php';
require_once __DIR__ . '/../app/Validation/Validator.php';
require_once __DIR__ . '/../app/Controllers/Admin/BaseAdminController.php';
require_once __DIR__ . '/../app/Controllers/Admin/AuthController.php';
require_once __DIR__ . '/../app/Controllers/Admin/DashboardController.php';
require_once __DIR__ . '/../app/Controllers/Admin/PagesController.php';
require_once __DIR__ . '/../app/Controllers/Admin/ServicesController.php';
require_once __DIR__ . '/../app/Controllers/Admin/ProjectsController.php';

// Router setup
$router = new Router();

// ---------------------------------------------------------------------------
// ADMIN ROUTES — M4.1 + M4.2 CRUD
// ---------------------------------------------------------------------------
// Auth
$router->get('/admin/login', 'Admin\AuthController@showLogin');
$router->post('/admin/login', 'Admin\AuthController@login');
$router->post('/admin/logout', 'Admin\AuthController@logout');

// Dashboard
$router->get('/admin/', 'Admin\DashboardController@index');
$router->get('/admin/dashboard', 'Admin\DashboardController@dashboard');

// Pages CRUD — M4.2
$router->get('/admin/pages', 'Admin\PagesController@index');
$router->get('/admin/pages/create', 'Admin\PagesController@create');
$router->post('/admin/pages', 'Admin\PagesController@store');
$router->get('/admin/pages/{id}/edit', 'Admin\PagesController@edit');
$router->post('/admin/pages/{id}/update', 'Admin\PagesController@update');
$router->post('/admin/pages/{id}/delete', 'Admin\PagesController@delete');

// Services CRUD — M4.2
$router->get('/admin/services', 'Admin\ServicesController@index');
$router->get('/admin/services/create', 'Admin\ServicesController@create');
$router->post('/admin/services', 'Admin\ServicesController@store');
$router->get('/admin/services/{id}/edit', 'Admin\ServicesController@edit');
$router->post('/admin/services/{id}/update', 'Admin\ServicesController@update');
$router->post('/admin/services/{id}/delete', 'Admin\ServicesController@delete');

// Projects CRUD — M4.2 (with technologies + features transactional)
$router->get('/admin/projects', 'Admin\ProjectsController@index');
$router->get('/admin/projects/create', 'Admin\ProjectsController@create');
$router->post('/admin/projects', 'Admin\ProjectsController@store');
$router->get('/admin/projects/{id}/edit', 'Admin\ProjectsController@edit');
$router->post('/admin/projects/{id}/update', 'Admin\ProjectsController@update');
$router->post('/admin/projects/{id}/delete', 'Admin\ProjectsController@delete');

// Placeholders for future CRUD (M4.3+) — still show "Coming in next milestone"
$router->get('/admin/blog', function(){ (new \App\Controllers\Admin\DashboardController())->placeholder('blog'); });
$router->get('/admin/testimonials', function(){ (new \App\Controllers\Admin\DashboardController())->placeholder('testimonials'); });
$router->get('/admin/settings', function(){ (new \App\Controllers\Admin\DashboardController())->placeholder('settings'); });
$router->get('/admin/navigation', function(){ (new \App\Controllers\Admin\DashboardController())->placeholder('navigation'); });
$router->get('/admin/seo', function(){ (new \App\Controllers\Admin\DashboardController())->placeholder('seo'); });
$router->get('/admin/media', function(){ (new \App\Controllers\Admin\DashboardController())->placeholder('media'); });
$router->get('/admin/messages', function(){ (new \App\Controllers\Admin\DashboardController())->placeholder('messages'); });
$router->get('/admin/account', function(){ (new \App\Controllers\Admin\DashboardController())->placeholder('account'); });

// ---------------------------------------------------------------------------
// Public routes (future, static still serves via files)
// ---------------------------------------------------------------------------
$router->get('/', function() {
    echo "Home — Future dynamic (M5+) — Static site currently serves index.html";
});
$router->get('/about/', function() { echo "About — Future dynamic"; });
$router->get('/services/', function() { echo "Services — Future dynamic"; });
$router->get('/projects/', function() { echo "Projects — Future dynamic"; });
$router->get('/projects/{slug}/', function($slug) { echo "Project detail: " . htmlspecialchars($slug); });
$router->get('/pricing/', function() { echo "Pricing — Future dynamic"; });
$router->get('/blog/', function() { echo "Blog archive — Future dynamic"; });
$router->get('/contact/', function() { echo "Contact — Future dynamic"; });
$router->get('/faq/', function() { echo "FAQ — Future dynamic"; });
$router->get('/booking/', function() { echo "Booking — Future dynamic"; });
$router->get('/terms/', function() { echo "Terms — Future dynamic"; });
$router->get('/privacy/', function() { echo "Privacy — Future dynamic"; });
$router->get('/{slug}/', function($slug) {
    if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        http_response_code(404);
        $view = new View();
        echo $view->renderError(404);
        exit;
    }
    echo "Blog post clean URL: " . htmlspecialchars($slug) . " — Future dynamic, will 301 legacy /blog/{slug}/ to here";
});
$router->post('/api/contact', function() {
    http_response_code(405);
    echo "Contact API — Future Milestone 6";
});

// Dispatch
try {
    $router->dispatch(Request::method(), Request::uri());
} catch (Exception $e) {
    error_log("Router error: " . $e->getMessage());
    if (Config::isDevelopment()) {
        http_response_code(500);
        echo "Error: " . htmlspecialchars($e->getMessage());
    } else {
        http_response_code(500);
        $view = new View();
        echo $view->renderError(500, 'Internal Server Error');
    }
}
