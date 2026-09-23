<?php
declare(strict_types=1);
namespace App\Models;
class Testimonial extends BaseModel {
    protected string $table = 'testimonials';
    public function getVisible(): array {
        $stmt = $this->db->prepare("SELECT * FROM testimonials WHERE is_visible=1 ORDER BY order_index ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
