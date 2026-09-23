<?php
declare(strict_types=1);

namespace App\Core;

use App\Config\Config;
use PDO;
use PDOException;

/**
 * Reusable PDO Database Layer
 * Milestone 3: Foundation
 * 
 * - PDO ERRMODE_EXCEPTION
 * - Emulated prepares disabled
 * - utf8mb4
 * - Reusable connection (singleton)
 * - Safe transaction handling
 */
class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $dbConfig = Config::get('db');

        if (!$dbConfig) {
            throw new \RuntimeException("Database config missing");
        }

        $host = $dbConfig['host'] ?? 'localhost';
        $name = $dbConfig['name'] ?? '';
        $user = $dbConfig['user'] ?? '';
        $pass = $dbConfig['pass'] ?? '';
        $charset = $dbConfig['charset'] ?? 'utf8mb4';
        $collation = $dbConfig['collation'] ?? 'utf8mb4_unicode_ci';
        $options = $dbConfig['options'] ?? [];

        $defaultOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES $charset COLLATE $collation",
        ];

        $options = $options + $defaultOptions;

        $dsn = "mysql:host=$host;dbname=$name;charset=$charset";

        try {
            self::$instance = new PDO($dsn, $user, $pass, $options);
            // Ensure utf8mb4 and strict mode
            self::$instance->exec("SET NAMES $charset COLLATE $collation");
            self::$instance->exec("SET time_zone = '+00:00'");
        } catch (PDOException $e) {
            // Log, don't expose credentials
            error_log("DB connection failed: " . $e->getMessage());
            if (Config::isDevelopment()) {
                throw $e;
            }
            throw new \RuntimeException("Database connection failed. Check config and ensure MySQL is running.");
        }

        return self::$instance;
    }

    public static function testConnection(): array
    {
        $result = [
            'php_version' => PHP_VERSION,
            'pdo_available' => extension_loaded('pdo'),
            'pdo_mysql_available' => extension_loaded('pdo_mysql'),
            'connected' => false,
            'version' => null,
            'charset' => null,
            'tables' => [],
            'error' => null,
        ];

        if (!extension_loaded('pdo')) {
            $result['error'] = 'PDO extension not loaded';
            return $result;
        }

        if (!extension_loaded('pdo_mysql')) {
            $result['error'] = 'PDO MySQL driver not loaded';
            return $result;
        }

        try {
            $pdo = self::getInstance();
            $result['connected'] = true;
            $result['version'] = $pdo->query('SELECT VERSION()')->fetchColumn();
            $result['charset'] = $pdo->query('SELECT @@character_set_database, @@collation_database')->fetch();

            $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            $result['tables'] = $tables;

            // Check JSON support (MySQL 5.7.8+ / MariaDB 10.2.7+)
            $pdo->query('SELECT JSON_VALID(\'{"a":1}\')')->fetchColumn();
            $result['json_support'] = true;

        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
            $result['connected'] = false;
        }

        return $result;
    }

    // Transaction helpers
    public static function beginTransaction(): void
    {
        self::getInstance()->beginTransaction();
    }

    public static function commit(): void
    {
        self::getInstance()->commit();
    }

    public static function rollBack(): void
    {
        $pdo = self::getInstance();
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }

    // For testing without MySQL in sandbox
    public static function isAvailable(): bool
    {
        return extension_loaded('pdo') && extension_loaded('pdo_mysql');
    }
}
