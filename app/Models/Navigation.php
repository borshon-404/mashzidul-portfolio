<?php
declare(strict_types=1);
namespace App\Models;
class Navigation extends BaseModel {
    protected string $table = 'navigation_items';
    public function getVisible(): array {
        $stmt = $this->db->prepare("SELECT * FROM navigation_items WHERE is_visible=1 ORDER BY order_index ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
