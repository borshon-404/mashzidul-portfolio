<?php
declare(strict_types=1);

/**
 * MTB Portfolio — Create First Admin Account (CLI Only)
 * Milestone 4.1
 * 
 * Usage:
 *   php scripts/create_admin.php
 * 
 * Security:
 * - CLI only (php_sapi_name() === 'cli')
 * - Never executable via HTTP (blocked by .htaccess)
 * - Prompts for name/email/password securely
 * - Validates email, enforces password policy, hashes bcrypt, PDO prepared, prevents duplicate
 * - Never exposes password, never prints password
 * 
 * cPanel/SSH:
 *   1. Upload project to /home/username/
 *   2. cPanel → Terminal or SSH: cd /home/username/mashzidul-portfolio (or wherever)
 *   3. php scripts/create_admin.php
 *   4. Follow prompts
 *   5. Delete script after or keep protected (CLI only + .htaccess deny)
 * 
 * If no SSH:
 *   Use phpMyAdmin manual fallback (documented below) — never store plaintext
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit("Forbidden: CLI only\n");
}

// Load config
$configPath = __DIR__ . '/../config/config.php';
$examplePath = __DIR__ . '/../config/config.example.php';

if (!file_exists($configPath)) {
    echo "Config not found: $configPath\n";
    echo "Copy config/config.example.php to config/config.php and fill DB creds\n";
    echo "Example: cp $examplePath $configPath\n";
    exit(1);
}

$config = require $configPath;

// Check PDO
if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
    echo "Error: PDO or pdo_mysql not loaded. Enable in cPanel → Select PHP Version\n";
    exit(1);
}

function prompt(string $msg): string {
    echo $msg;
    $input = trim(fgets(STDIN));
    return $input;
}

function promptPassword(string $msg): string {
    echo $msg;
    // Try to hide input if possible (stty -echo)
    if (function_exists('shell_exec') && stripos(PHP_OS, 'WIN') === false) {
        shell_exec('stty -echo');
        $pass = trim(fgets(STDIN));
        shell_exec('stty echo');
        echo "\n";
        return $pass;
    }
    // Fallback: visible but still not logged
    return trim(fgets(STDIN));
}

function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePassword(string $password): array {
    $errors = [];
    if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters";
    if (!preg_match('/[A-Z]/', $password)) $errors[] = "Password must contain uppercase letter";
    if (!preg_match('/[a-z]/', $password)) $errors[] = "Password must contain lowercase letter";
    if (!preg_match('/[0-9]/', $password)) $errors[] = "Password must contain number";
    if (!preg_match('/[^A-Za-z0-9]/', $password)) $errors[] = "Password must contain symbol";
    $common = ['password','12345678','admin123','qwerty123'];
    if (in_array(strtolower($password), $common, true)) $errors[] = "Password too common";
    return $errors;
}

echo "=== MTB Portfolio — Create Admin Account ===\n";
echo "Milestone 4.1 — CLI Only, Secure\n\n";

// Prompt name
$name = '';
while (true) {
    $name = prompt("Name (e.g., Mashzidul Tanun Borshon): ");
    if (strlen(trim($name)) >= 2) break;
    echo "Name must be at least 2 characters\n";
}

// Prompt email
$email = '';
while (true) {
    $email = prompt("Email: ");
    if (!validateEmail($email)) {
        echo "Invalid email format\n";
        continue;
    }
    break;
}

// Prompt password securely
$password = '';
while (true) {
    $password = promptPassword("Password (min 8 chars, upper/lower/number/symbol): ");
    $errors = validatePassword($password);
    if (!empty($errors)) {
        echo "Password policy failed:\n";
        foreach ($errors as $e) echo " - $e\n";
        continue;
    }
    $confirm = promptPassword("Confirm Password: ");
    if ($password !== $confirm) {
        echo "Passwords do not match\n";
        continue;
    }
    break;
}

// Hash
$hash = password_hash($password, PASSWORD_BCRYPT);
if (!$hash) {
    echo "Error: Failed to hash password\n";
    exit(1);
}

// DB connection via PDO
$dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s',
    $config['db']['host'] ?? 'localhost',
    $config['db']['name'] ?? '',
    $config['db']['charset'] ?? 'utf8mb4'
);

try {
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['name'] ? $config['db']['user'] : '', $config['db']['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Check duplicate email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email=:email LIMIT 1");
    $stmt->execute([':email'=>$email]);
    if ($stmt->fetchColumn()) {
        echo "Error: Email already exists: $email\n";
        exit(1);
    }

    // Insert
    $stmt = $pdo->prepare("
        INSERT INTO users (email, password_hash, name, role, is_active)
        VALUES (:email, :hash, :name, 'admin', 1)
    ");

    $stmt->execute([
        ':email'=>$email,
        ':hash'=>$hash,
        ':name'=>$name,
    ]);

    $id = $pdo->lastInsertId();

    echo "\n✅ Admin account created successfully!\n";
    echo "ID: $id\n";
    echo "Email: $email\n";
    echo "Name: $name\n";
    echo "Role: admin\n";
    echo "\nYou can now login at /admin/login\n";
    echo "IMPORTANT: Delete this script or keep protected (CLI only + .htaccess deny)\n";

    // Never print password or hash

} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
    echo "Check config.php DB credentials and ensure schema.sql imported\n";
    exit(1);
}

/*
 * Fallback without SSH — phpMyAdmin manual (safe, no plaintext stored):
 * 
 * 1. cPanel → phpMyAdmin → Select DB → users table → Insert
 * 2. For password_hash, you need bcrypt hash. Generate locally:
 *    - On your local machine with PHP: php -r "echo password_hash('YourStrongPass123!', PASSWORD_BCRYPT);"
 *    - Copy the resulting $2y$10$... hash (60 chars)
 *    - In phpMyAdmin, insert email, paste hash into password_hash, name, role admin, is_active 1
 * 3. Never store plaintext password in DB, only hash
 * 4. Never share hash publicly
 * 
 * Example local generation:
 *   php -r "echo password_hash('MyStr0ng!Pass', PASSWORD_BCRYPT) . PHP_EOL;"
 */
