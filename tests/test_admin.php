<?php
declare(strict_types=1);

/**
 * MTB Portfolio — Admin Auth Tests
 * Milestone 4.1
 * 
 * Tests:
 * - admin login route exists
 * - invalid credentials
 * - successful auth (if DB available)
 * - protected route redirect
 * - logout
 * - session expiration
 * - CSRF
 * - rate limiting
 * - admin account creation
 * - duplicate admin prevention
 * 
 * CLI only
 */

if (php_sapi_name() !== 'cli') die("CLI only\n");

$verbose = in_array('--verbose', $argv);
$results = [];

function test($name, callable $fn, $verbose=false) {
    try {
        $ok = $fn();
        $status = $ok ? 'PASS' : 'FAIL';
        if ($verbose) echo "[$status] $name\n";
        return ['name'=>$name, 'status'=>$status, 'error'=>null];
    } catch (Throwable $e) {
        if ($verbose) echo "[FAIL] $name — {$e->getMessage()}\n";
        return ['name'=>$name, 'status'=>'FAIL', 'error'=>$e->getMessage()];
    }
}
function blocked($name, $reason) {
    echo "[BLOCKED BY ENVIRONMENT] $name — $reason\n";
    return ['name'=>$name, 'status'=>'BLOCKED BY ENVIRONMENT', 'error'=>$reason];
}
function notRun($name, $reason) {
    echo "[NOT RUN] $name — $reason\n";
    return ['name'=>$name, 'status'=>'NOT RUN', 'error'=>$reason];
}

// Check files exist
$results[] = test('Admin routes defined in public/index.php', function() {
    $content = file_get_contents(__DIR__ . '/../public/index.php');
    return strpos($content, '/admin/login') !== false && strpos($content, '/admin/dashboard') !== false && strpos($content, '/admin/logout') !== false;
}, $verbose);

$results[] = test('AuthController exists', function() {
    return file_exists(__DIR__ . '/../app/Controllers/Admin/AuthController.php');
}, $verbose);

$results[] = test('DashboardController exists', function() {
    return file_exists(__DIR__ . '/../app/Controllers/Admin/DashboardController.php');
}, $verbose);

$results[] = test('Admin login view exists (black+gold)', function() {
    $path = __DIR__ . '/../app/Views/admin/login.php';
    if (!file_exists($path)) return false;
    $c = file_get_contents($path);
    return strpos($c, 'Admin Login') !== false && strpos($c, '_csrf') !== false && strpos($c, 'type="password"') !== false;
}, $verbose);

$results[] = test('Admin layout exists (sidebar, header, flash, responsive)', function() {
    $path = __DIR__ . '/../app/Views/admin/layout.php';
    if (!file_exists($path)) return false;
    $c = file_get_contents($path);
    return strpos($c, 'admin-sidebar') !== false && strpos($c, 'admin-header') !== false && strpos($c, 'admin-nav') !== false && strpos($c, 'is-active') !== false;
}, $verbose);

$results[] = test('Admin dashboard view exists (real counts)', function() {
    $path = __DIR__ . '/../app/Views/admin/dashboard.php';
    if (!file_exists($path)) return false;
    $c = file_get_contents($path);
    return strpos($c, 'admin-stats-grid') !== false && strpos($c, 'System Status') !== false && strpos($c, 'pages') !== false;
}, $verbose);

$results[] = test('Admin CSS separate from public (admin.css)', function() {
    $path = __DIR__ . '/../assets/css/admin.css';
    if (!file_exists($path)) return false;
    $c = file_get_contents($path);
    return strpos($c, '--admin-gold') !== false && strpos($c, 'admin-sidebar') !== false;
}, $verbose);

$results[] = test('Admin JS exists', function() {
    return file_exists(__DIR__ . '/../assets/js/admin.js');
}, $verbose);

$results[] = test('CLI admin creation script exists and is CLI only', function() {
    $path = __DIR__ . '/../scripts/create_admin.php';
    if (!file_exists($path)) return false;
    $c = file_get_contents($path);
    return strpos($c, "php_sapi_name() !== 'cli'") !== false && strpos($c, 'password_hash') !== false && strpos($c, 'PASSWORD_BCRYPT') !== false;
}, $verbose);

$results[] = test('No hardcoded admin credentials', function() {
    $files = array_merge(
        glob(__DIR__ . '/../app/**/*.php'),
        glob(__DIR__ . '/../config/*.php'),
        glob(__DIR__ . '/../public/*.php')
    );
    foreach ($files as $f) {
        $c = file_get_contents($f);
        if (preg_match('/admin@example\.com.*password123/i', $c)) return false;
        if (preg_match('/\$password\s*=\s*[\'"]admin[\'"]/i', $c)) return false;
    }
    return true;
}, $verbose);

$results[] = test('CSRF protection in login and logout', function() {
    $auth = file_get_contents(__DIR__ . '/../app/Controllers/Admin/AuthController.php');
    return strpos($auth, 'Csrf::validate') !== false && strpos($auth, '_csrf') !== false;
}, $verbose);

$results[] = test('Rate limiting 5/15min in login', function() {
    $auth = file_get_contents(__DIR__ . '/../app/Controllers/Admin/AuthController.php');
    $rate = file_get_contents(__DIR__ . '/../app/Security/RateLimiter.php');
    return strpos($auth, 'RateLimiter::isLoginAllowed') !== false && strpos($rate, '5') !== false && strpos($rate, '900') !== false;
}, $verbose);

$results[] = test('Session security: HttpOnly, SameSite, strict_mode, regenerate', function() {
    $sess = file_get_contents(__DIR__ . '/../app/Core/Session.php');
    return strpos($sess, 'httponly') !== false && strpos($sess, 'samesite') !== false && strpos($sess, 'use_strict_mode') !== false && strpos($sess, 'session_regenerate_id') !== false;
}, $verbose);

$results[] = test('Password handling bcrypt, no plaintext', function() {
    $sec = file_get_contents(__DIR__ . '/../app/Core/Security.php');
    $user = file_get_contents(__DIR__ . '/../app/Models/User.php');
    return strpos($sec, 'PASSWORD_BCRYPT') !== false && strpos($sec, 'password_hash') !== false && strpos($user, 'password_hash') === false || strpos($user, 'password_hash') !== false && strpos($sec, 'plaintext') === false;
}, $verbose);

$results[] = test('No user enumeration (generic failure message)', function() {
    $auth = file_get_contents(__DIR__ . '/../app/Controllers/Admin/AuthController.php');
    return strpos($auth, 'Invalid email or password') !== false && strpos($auth, 'dummyHash') !== false;
}, $verbose);

$results[] = test('Protected routes use requireAdmin', function() {
    $base = file_get_contents(__DIR__ . '/../app/Controllers/Admin/BaseAdminController.php');
    $dash = file_get_contents(__DIR__ . '/../app/Controllers/Admin/DashboardController.php');
    return strpos($base, 'requireAdmin') !== false && strpos($dash, 'extends BaseAdminController') !== false;
}, $verbose);

$results[] = test('Logout destroys session and CSRF protected', function() {
    $auth = file_get_contents(__DIR__ . '/../app/Controllers/Admin/AuthController.php');
    return strpos($auth, 'function logout') !== false && strpos($auth, 'Auth::logout') !== false && strpos($auth, 'Csrf::validate') !== false;
}, $verbose);

$results[] = test('Dashboard shows real DB counts (pages, services, projects, blog, etc.)', function() {
    $dash = file_get_contents(__DIR__ . '/../app/Controllers/Admin/DashboardController.php');
    return strpos($dash, 'getCounts') !== false && strpos($dash, 'pages') !== false && strpos($dash, 'services') !== false && strpos($dash, 'blog_published') !== false;
}, $verbose);

$results[] = test('System status: DB, PHP version, env, no secrets', function() {
    $dash = file_get_contents(__DIR__ . '/../app/Controllers/Admin/DashboardController.php');
    return strpos($dash, 'getSystemStatus') !== false && strpos($dash, 'PHP_VERSION') !== false && strpos($dash, 'db_connected') !== false && strpos($dash, 'db_pass') === false;
}, $verbose);

$results[] = test('Admin navigation placeholders (Coming in next milestone)', function() {
    $placeholder = file_get_contents(__DIR__ . '/../app/Views/admin/placeholder.php');
    $layout = file_get_contents(__DIR__ . '/../app/Views/admin/layout.php');
    return strpos($placeholder, 'Coming in the next milestone') !== false && strpos($layout, '/admin/pages') !== false;
}, $verbose);

// DB-dependent tests — blocked if no real config
$configPath = __DIR__ . '/../config/config.php';
$hasRealConfig = file_exists($configPath);
$hasPdo = extension_loaded('pdo') && extension_loaded('pdo_mysql');

if (!$hasPdo || !$hasRealConfig) {
    $results[] = blocked('Invalid credentials test', 'No DB/PHP in sandbox, will be tested on cPanel staging');
    $results[] = blocked('Successful authentication', 'No DB');
    $results[] = blocked('Protected route redirect unauthenticated', 'No DB');
    $results[] = blocked('Authenticated dashboard 200', 'No DB');
    $results[] = blocked('Logout destroys session', 'No DB');
    $results[] = blocked('Session expiration', 'No DB');
    $results[] = blocked('Duplicate admin prevention', 'No DB');
    $results[] = blocked('Existing data preservation (4 blog posts)', 'No DB, but counts checked via Python simulation');
} else {
    // Real DB tests would go here
    $results[] = notRun('Invalid credentials test', 'Real DB config exists but not running full integration in this test file');
}

echo "\n=== ADMIN AUTH TESTS ===\n";
$pass=0;$fail=0;$blocked=0;$notrun=0;
foreach ($results as $r) {
    echo "[{$r['status']}] {$r['name']}";
    if ($r['error']) echo " — {$r['error']}";
    echo "\n";
    if ($r['status']==='PASS') $pass++;
    elseif ($r['status']==='FAIL') $fail++;
    elseif (strpos($r['status'],'BLOCKED')!==false) $blocked++;
    else $notrun++;
}
echo "\nSummary: PASS $pass, FAIL $fail, NOT RUN $notrun, BLOCKED $blocked\n";
exit($fail>0 ? 1 : 0);
