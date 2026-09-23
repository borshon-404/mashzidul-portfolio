<?php
declare(strict_types=1);
namespace App\Models;
class SiteSettings extends BaseModel {
    protected string $table = 'site_settings';
    public function get(): ?array {
        $stmt = $this->db->query("SELECT * FROM site_settings WHERE id=1 LIMIT 1");
        return $stmt->fetch() ?: null;
    }
    public function getSocialLinks(): array {
        $settings = $this->get();
        if (!$settings || empty($settings['social_links'])) return [];
        $links = json_decode($settings['social_links'], true);
        return is_array($links) ? $links : [];
    }
}
