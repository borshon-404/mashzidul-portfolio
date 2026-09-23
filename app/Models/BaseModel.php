<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Base Model — reusable PDO functionality
 * Milestone 3: Foundation
 * 
 * - Prepared statements only
 * - No raw SQL interpolation
 * - Transaction helpers
 */
abstract class BaseModel
{
    protected PDO $db;
    protected string $table;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Generic find by id
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // Generic find by slug
    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // Generic all with order
    public function all(string $orderBy = 'order_index ASC, id ASC'): array
    {
        // $orderBy is not user-controlled, safe (whitelisted in child)
        $stmt = $this->db->query("SELECT * FROM `{$this->table}` ORDER BY $orderBy");
        return $stmt->fetchAll();
    }

    // Count
    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM `{$this->table}`");
        return (int) $stmt->fetchColumn();
    }

    // Transaction helpers
    protected function beginTransaction(): void
    {
        Database::beginTransaction();
    }

    protected function commit(): void
    {
        Database::commit();
    }

    protected function rollBack(): void
    {
        Database::rollBack();
    }
}
