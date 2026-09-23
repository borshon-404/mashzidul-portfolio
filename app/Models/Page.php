<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;
use PDO;

class Page extends BaseModel {
    protected string $table = 'pages';

    public function getBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM pages WHERE slug=:slug AND is_visible=1 LIMIT 1");
        $stmt->execute([':slug'=>$slug]);
        return $stmt->fetch() ?: null;
    }

    public function getAllVisible(): array {
        $stmt = $this->db->query("SELECT * FROM pages WHERE is_visible=1 ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    // Admin: all pages including hidden, ordered by updated_at DESC
    public function getAllForAdmin(): array {
        $stmt = $this->db->query("SELECT * FROM pages ORDER BY updated_at DESC, id ASC");
        return $stmt->fetchAll();
    }

    public function findByIdAdmin(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM pages WHERE id=:id LIMIT 1");
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch() ?: null;
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT id FROM pages WHERE slug=:slug AND id!=:excludeId LIMIT 1");
            $stmt->execute([':slug'=>$slug, ':excludeId'=>$excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM pages WHERE slug=:slug LIMIT 1");
            $stmt->execute([':slug'=>$slug]);
        }
        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO pages 
            (slug, title, meta_title, meta_description, canonical_url, og_title, og_description, og_image_id, twitter_title, twitter_description, twitter_image_id, robots, is_visible)
            VALUES (:slug, :title, :meta_title, :meta_description, :canonical_url, :og_title, :og_description, :og_image_id, :twitter_title, :twitter_description, :twitter_image_id, :robots, :is_visible)
        ");
        $stmt->execute([
            ':slug' => $data['slug'],
            ':title' => $data['title'] ?? null,
            ':meta_title' => $data['meta_title'] ?? null,
            ':meta_description' => $data['meta_description'] ?? null,
            ':canonical_url' => $data['canonical_url'] ?? null,
            ':og_title' => $data['og_title'] ?? null,
            ':og_description' => $data['og_description'] ?? null,
            ':og_image_id' => $data['og_image_id'] ?? null,
            ':twitter_title' => $data['twitter_title'] ?? null,
            ':twitter_description' => $data['twitter_description'] ?? null,
            ':twitter_image_id' => $data['twitter_image_id'] ?? null,
            ':robots' => $data['robots'] ?? 'index, follow',
            ':is_visible' => $data['is_visible'] ?? 1,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE pages SET
                slug=:slug,
                title=:title,
                meta_title=:meta_title,
                meta_description=:meta_description,
                canonical_url=:canonical_url,
                og_title=:og_title,
                og_description=:og_description,
                og_image_id=:og_image_id,
                twitter_title=:twitter_title,
                twitter_description=:twitter_description,
                twitter_image_id=:twitter_image_id,
                robots=:robots,
                is_visible=:is_visible,
                updated_at=CURRENT_TIMESTAMP
            WHERE id=:id
        ");
        return $stmt->execute([
            ':slug' => $data['slug'],
            ':title' => $data['title'] ?? null,
            ':meta_title' => $data['meta_title'] ?? null,
            ':meta_description' => $data['meta_description'] ?? null,
            ':canonical_url' => $data['canonical_url'] ?? null,
            ':og_title' => $data['og_title'] ?? null,
            ':og_description' => $data['og_description'] ?? null,
            ':og_image_id' => $data['og_image_id'] ?? null,
            ':twitter_title' => $data['twitter_title'] ?? null,
            ':twitter_description' => $data['twitter_description'] ?? null,
            ':twitter_image_id' => $data['twitter_image_id'] ?? null,
            ':robots' => $data['robots'] ?? 'index, follow',
            ':is_visible' => $data['is_visible'] ?? 1,
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool {
        // Check for dependent page_sections - they will CASCADE, but we note count
        $stmt = $this->db->prepare("DELETE FROM pages WHERE id=:id");
        return $stmt->execute([':id'=>$id]);
    }

    public function countSections(int $pageId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM page_sections WHERE page_id=:pid");
        $stmt->execute([':pid'=>$pageId]);
        return (int) $stmt->fetchColumn();
    }
}
