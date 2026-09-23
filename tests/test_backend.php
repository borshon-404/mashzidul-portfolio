<?php
declare(strict_types=1);

/**
 * MTB Portfolio — Backend Foundation Tests
 * Milestone 3
 * 
 * Tests:
 * 1. PHP syntax (via php -l)
 * 2. Config loading
 * 3. DB connection
 * 4. PDO init
 * 5. Table detection
 * 6. Prepared queries
 * 7. Transaction rollback
 * 8. Transaction commit
 * 9. 404 handling
 * 10. 403 handling
 * 11. 405 handling
 * 12. Output escaping
 * 13. CSRF generation/validation
 * 14. Session init
 * 15. password_hash/verify
 * 16. Auth helper
 * 17. Published vs draft visibility
 * 18. Slug lookup
 * 19. Related posts query
 * 20. Media path safety
 * 21. Uploads PHP protection
 * 22. Static build
 * 
 * Run: php tests/test_backend.php
 * Or: php tests/test_backend.php --verbose
 */

if (php_sapi_name() !== 'cli') {
    die("CLI only\n");
}

$verbose = in_array('--verbose', $argv);
$results = [];

function test(string $name, callable $fn, bool $verbose = false): array {
    $start = microtime(true);
    try {
        $result = $fn();
        $status = $result ? 'PASS' : 'FAIL';
        $time = round((microtime(true)-$start)*1000, 2);
        if ($verbose) echo "[$status] $name ({$time}ms)\n";
        return ['name'=>$name, 'status'=>$status, 'time'=>$time, 'error'=>null];
    } catch (Throwable $e) {
        $time = round((microtime(true)-$start)*1000, 2);
        $status = 'FAIL';
        if ($verbose) echo "[$status] $name — Exception: {$e->getMessage()} ({$time}ms)\n";
        return ['name'=>$name, 'status'=>$status, 'time'=>$time, 'error'=>$e->getMessage()];
    }
}

function testNotRun(string $name, string $reason): array {
    return ['name'=>$name, 'status'=>'NOT RUN', 'time'=>0, 'error'=>$reason];
}

function testBlocked(string $name, string $reason): array {
    return ['name'=>$name, 'status'=>'BLOCKED BY ENVIRONMENT', 'time'=>0, 'error'=>$reason];
}

// ---------------------------------------------------------------------------
// 1. PHP Syntax — check all backend files via php -l
// ---------------------------------------------------------------------------
$results[] = test('1. PHP syntax', function() {
    $files = array_merge(
        glob(__DIR__ . '/../app/**/*.php'),
        glob(__DIR__ . '/../app/*.php'),
        glob(__DIR__ . '/../config/*.php'),
        glob(__DIR__ . '/../public/*.php'),
        glob(__DIR__ . '/../database/migrations/*.php')
    );
    foreach ($files as $file) {
        $output = [];
        $ret = 0;
        exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $ret);
        if ($ret !== 0) {
            throw new Exception("Syntax error in $file: " . implode("\n", $output));
        }
    }
    return true;
}, $verbose);

// ---------------------------------------------------------------------------
// 2. Config loading
// ---------------------------------------------------------------------------
$results[] = test('2. Config loading', function() {
    require_once __DIR__ . '/../app/Config/Config.php';
    $cfg = \App\Config\Config::load(__DIR__ . '/../config/config.example.php');
    return is_array($cfg) && isset($cfg['db']);
}, $verbose);

// ---------------------------------------------------------------------------
// 3-8. DB tests — only if PDO and config available
// ---------------------------------------------------------------------------
$configPath = __DIR__ . '/../config/config.php';
$hasRealConfig = file_exists($configPath);
$hasPdo = extension_loaded('pdo') && extension_loaded('pdo_mysql');

if (!$hasPdo) {
    $results[] = testBlocked('3. DB connection', 'PDO or pdo_mysql not loaded in sandbox');
    $results[] = testBlocked('4. PDO init', 'PDO not available');
    $results[] = testBlocked('5. Table detection', 'DB not connected');
    $results[] = testBlocked('6. Prepared queries', 'DB not connected');
    $results[] = testBlocked('7. Transaction rollback', 'DB not connected');
    $results[] = testBlocked('8. Transaction commit', 'DB not connected');
} elseif (!$hasRealConfig) {
    $results[] = testNotRun('3. DB connection', 'No real config.php, only example — use config.example.php for structure test, real DB test requires cPanel creds');
    $results[] = testNotRun('4. PDO init', 'No real DB config');
    $results[] = testNotRun('5. Table detection', 'No real DB');
    $results[] = testNotRun('6. Prepared queries', 'No real DB');
    $results[] = testNotRun('7. Transaction rollback', 'No real DB');
    $results[] = testNotRun('8. Transaction commit', 'No real DB');
} else {
    // Real DB tests
    $results[] = test('3. DB connection', function() {
        require_once __DIR__ . '/../app/Core/Database.php';
        $test = \App\Core\Database::testConnection();
        return $test['connected'] === true;
    }, $verbose);

    $results[] = test('4. PDO init', function() {
        $pdo = \App\Core\Database::getInstance();
        return $pdo instanceof PDO;
    }, $verbose);

    $results[] = test('5. Table detection', function() {
        $pdo = \App\Core\Database::getInstance();
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        return count($tables) >= 17;
    }, $verbose);

    $results[] = test('6. Prepared queries', function() {
        $pdo = \App\Core\Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug=:slug LIMIT 1");
        $stmt->execute([':slug'=>'home']);
        return true;
    }, $verbose);

    $results[] = test('7. Transaction rollback', function() {
        $pdo = \App\Core\Database::getInstance();
        $pdo->beginTransaction();
        $pdo->exec("INSERT INTO pages (slug, title) VALUES ('test-rollback-".bin2hex(random_bytes(4))."', 'Test')");
        $pdo->rollBack();
        return true;
    }, $verbose);

    $results[] = test('8. Transaction commit', function() {
        $pdo = \App\Core\Database::getInstance();
        $pdo->beginTransaction();
        $slug = 'test-commit-' . bin2hex(random_bytes(4));
        $pdo->prepare("INSERT INTO pages (slug, title) VALUES (:slug, 'Test')")->execute([':slug'=>$slug]);
        $pdo->commit();
        // Cleanup
        $pdo->prepare("DELETE FROM pages WHERE slug=:slug")->execute([':slug'=>$slug]);
        return true;
    }, $verbose);
}

// ---------------------------------------------------------------------------
// 9-11. Error handling
// ---------------------------------------------------------------------------
$results[] = test('9. 404 handling', function() {
    require_once __DIR__ . '/../app/Core/View.php';
    $view = new \App\Core\View();
    $html = $view->renderError(404);
    return strpos($html, '404') !== false;
}, $verbose);

$results[] = test('10. 403 handling', function() {
    $view = new \App\Core\View();
    $html = $view->renderError(403);
    return strpos($html, '403') !== false;
}, $verbose);

$results[] = test('11. 405 handling', function() {
    $view = new \App\Core\View();
    $html = $view->renderError(405);
    return strpos($html, '405') !== false;
}, $verbose);

// ---------------------------------------------------------------------------
// 12. Output escaping
// ---------------------------------------------------------------------------
$results[] = test('12. Output escaping', function() {
    require_once __DIR__ . '/../app/Core/Security.php';
    $xss = '<script>alert(1)</script>';
    $escaped = \App\Core\Security::e($xss);
    return $escaped === '&lt;script&gt;alert(1)&lt;/script&gt;' && strpos($escaped, '<script>') === false;
}, $verbose);

// ---------------------------------------------------------------------------
// 13. CSRF
// ---------------------------------------------------------------------------
$results[] = test('13. CSRF token generation/validation', function() {
    require_once __DIR__ . '/../app/Core/Session.php';
    require_once __DIR__ . '/../app/Security/Csrf.php';
    // Start session
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    $token = \App\Security\Csrf::generate();
    return \App\Security\Csrf::validate($token) && !\App\Security\Csrf::validate('invalid');
}, $verbose);

// ---------------------------------------------------------------------------
// 14. Session init
// ---------------------------------------------------------------------------
$results[] = test('14. Session initialization', function() {
    require_once __DIR__ . '/../app/Core/Session.php';
    \App\Core\Session::start();
    \App\Core\Session::set('test_key', 'test_value');
    return \App\Core\Session::get('test_key') === 'test_value';
}, $verbose);

// ---------------------------------------------------------------------------
// 15. password_hash/verify
// ---------------------------------------------------------------------------
$results[] = test('15. password_hash/password_verify', function() {
    $pass = 'TestPassword123!';
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    return password_verify($pass, $hash) && !password_verify('wrong', $hash);
}, $verbose);

// ---------------------------------------------------------------------------
// 16. Auth helper
// ---------------------------------------------------------------------------
$results[] = test('16. Authorization helper', function() {
    require_once __DIR__ . '/../app/Core/Auth.php';
    // Should not be logged in in test
    \App\Core\Session::start();
    $_SESSION = []; // Clear
    return \App\Core\Auth::check() === false;
}, $verbose);

// ---------------------------------------------------------------------------
// 17-19. Blog visibility, slug lookup, related
// ---------------------------------------------------------------------------
if (!$hasPdo || !$hasRealConfig) {
    $results[] = testBlocked('17. Published vs draft visibility', 'DB not available');
    $results[] = testBlocked('18. Slug lookup', 'DB not available');
    $results[] = testBlocked('19. Related posts query', 'DB not available');
} else {
    $results[] = test('17. Published vs draft visibility', function() {
        $pdo = \App\Core\Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE status='published' AND is_visible=1 LIMIT 1");
        $stmt->execute();
        $published = $stmt->fetch();
        // Ensure draft not returned in public method
        require_once __DIR__ . '/../app/Models/BlogPost.php';
        $model = new \App\Models\BlogPost();
        $publicPosts = $model->getPublished();
        foreach ($publicPosts as $p) {
            if ($p['status'] !== 'published' || $p['is_visible'] != 1) return false;
        }
        return true;
    }, $verbose);

    $results[] = test('18. Slug lookup', function() {
        require_once __DIR__ . '/../app/Models/BlogPost.php';
        $model = new \App\Models\BlogPost();
        $post = $model->getBySlug('website-speed-optimization');
        return $post && $post['slug'] === 'website-speed-optimization';
    }, $verbose);

    $results[] = test('19. Related posts query', function() {
        require_once __DIR__ . '/../app/Models/BlogPost.php';
        $model = new \App\Models\BlogPost();
        $posts = $model->getPublished(1);
        if (empty($posts)) return false;
        $related = $model->getRelated((int)$posts[0]['id'], 3);
        return is_array($related) && count($related) <= 3;
    }, $verbose);
}

// ---------------------------------------------------------------------------
// 20-21. Media path safety, uploads protection
// ---------------------------------------------------------------------------
$results[] = test('20. Media path safety', function() {
    require_once __DIR__ . '/../app/Core/Security.php';
    $safe = \App\Core\Security::sanitizeFilename('../../etc/passwd');
    return strpos($safe, '..') === false && strpos($safe, '/') === false;
}, $verbose);

$results[] = test('21. Uploads PHP execution protection', function() {
    $htaccess = __DIR__ . '/../uploads/.htaccess';
    if (!file_exists($htaccess)) return false;
    $content = file_get_contents($htaccess);
    return strpos($content, 'php_flag engine off') !== false && strpos($content, 'Require all denied') !== false;
}, $verbose);

// ---------------------------------------------------------------------------
// 22. Static build
// ---------------------------------------------------------------------------
$results[] = test('22. Existing static build', function() {
    // Check if build.py exists and can be parsed
    $buildPath = __DIR__ . '/../site_src/build.py';
    if (!file_exists($buildPath)) return false;
    // Check if index.html exists (built)
    $indexPath = __DIR__ . '/../index.html';
    return file_exists($indexPath);
}, $verbose);

// ---------------------------------------------------------------------------
// Summary
// ---------------------------------------------------------------------------
echo "\n=== BACKEND FOUNDATION TESTS ===\n";
$pass = 0; $fail = 0; $notRun = 0; $blocked = 0;
foreach ($results as $r) {
    $status = $r['status'];
    if ($status === 'PASS') $pass++;
    elseif ($status === 'FAIL') $fail++;
    elseif ($status === 'NOT RUN') $notRun++;
    elseif (strpos($status, 'BLOCKED') !== false) $blocked++;

    echo sprintf("[%s] %s", $status, $r['name']);
    if ($r['error']) echo " — {$r['error']}";
    echo "\n";
}

echo "\nSummary: PASS $pass, FAIL $fail, NOT RUN $notRun, BLOCKED $blocked\n";

if ($fail > 0) {
    echo "Some tests FAILED — check errors above\n";
    exit(1);
}

echo "All runnable tests PASSED\n";
exit(0);
