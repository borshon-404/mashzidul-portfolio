<?php
declare(strict_types=1);
namespace App\Models;
class BlogTag extends BaseModel {
    protected string $table = 'blog_tags';
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM blog_tags ORDER BY name ASC");
        return $stmt->fetchAll();
    }
    public function getByPostId(int $postId): array {
        $stmt = $this->db->prepare("
            SELECT t.* FROM blog_tags t
            JOIN blog_post_tags pt ON t.id = pt.tag_id
            WHERE pt.post_id=:pid ORDER BY t.name ASC
        ");
        $stmt->execute([':pid'=>$postId]);
        return $stmt->fetchAll();
    }
}
