<?php
declare(strict_types=1);
namespace App\Models;

class Service extends BaseModel {
    protected string $table = 'services';

    public function getAllVisible(): array {
        $stmt = $this->db->prepare("SELECT * FROM services WHERE is_visible=1 ORDER BY order_index ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM services WHERE slug=:slug AND is_visible=1 LIMIT 1");
        $stmt->execute([':slug'=>$slug]);
        return $stmt->fetch() ?: null;
    }

    public function getAllForAdmin(): array {
        $stmt = $this->db->query("SELECT * FROM services ORDER BY order_index ASC, id ASC");
        return $stmt->fetchAll();
    }

    public function findByIdAdmin(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM services WHERE id=:id LIMIT 1");
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch() ?: null;
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT id FROM services WHERE slug=:slug AND id!=:excludeId LIMIT 1");
            $stmt->execute([':slug'=>$slug, ':excludeId'=>$excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM services WHERE slug=:slug LIMIT 1");
            $stmt->execute([':slug'=>$slug]);
        }
        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO services (slug, title, description, icon_key, order_index, is_visible)
            VALUES (:slug, :title, :description, :icon_key, :order_index, :is_visible)
        ");
        $stmt->execute([
            ':slug' => $data['slug'],
            ':title' => $data['title'],
            ':description' => $data['description'] ?? null,
            ':icon_key' => $data['icon_key'] ?? null,
            ':order_index' => $data['order_index'] ?? 0,
            ':is_visible' => $data['is_visible'] ?? 1,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE services SET
                slug=:slug,
                title=:title,
                description=:description,
                icon_key=:icon_key,
                order_index=:order_index,
                is_visible=:is_visible,
                updated_at=CURRENT_TIMESTAMP
            WHERE id=:id
        ");
        return $stmt->execute([
            ':slug' => $data['slug'],
            ':title' => $data['title'],
            ':description' => $data['description'] ?? null,
            ':icon_key' => $data['icon_key'] ?? null,
            ':order_index' => $data['order_index'] ?? 0,
            ':is_visible' => $data['is_visible'] ?? 1,
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM services WHERE id=:id");
        return $stmt->execute([':id'=>$id]);
    }
}
