<?php
declare(strict_types=1);
namespace App\Models;
class PageSection extends BaseModel {
    protected string $table = 'page_sections';
    public function getByPageId(int $pageId): array {
        $stmt = $this->db->prepare("SELECT * FROM page_sections WHERE page_id=:page_id AND is_visible=1 ORDER BY order_index ASC");
        $stmt->execute([':page_id'=>$pageId]);
        return $stmt->fetchAll();
    }
    public function getByPageSlug(string $pageSlug): array {
        $stmt = $this->db->prepare("
            SELECT ps.* FROM page_sections ps
            JOIN pages p ON ps.page_id = p.id
            WHERE p.slug=:slug AND ps.is_visible=1
            ORDER BY ps.order_index ASC
        ");
        $stmt->execute([':slug'=>$pageSlug]);
        return $stmt->fetchAll();
    }
}
