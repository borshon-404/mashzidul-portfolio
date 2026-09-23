<?php
declare(strict_types=1);
namespace App\Models;
class BlogCategory extends BaseModel {
    protected string $table = 'blog_categories';
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM blog_categories ORDER BY name ASC");
        return $stmt->fetchAll();
    }
    public function getBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM blog_categories WHERE slug=:slug LIMIT 1");
        $stmt->execute([':slug'=>$slug]);
        return $stmt->fetch() ?: null;
    }
}
