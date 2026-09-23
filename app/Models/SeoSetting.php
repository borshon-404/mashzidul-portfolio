<?php
declare(strict_types=1);
namespace App\Models;
class SeoSetting extends BaseModel {
    protected string $table = 'seo_settings';
    public function getByPageType(string $pageType): ?array {
        $stmt = $this->db->prepare("SELECT * FROM seo_settings WHERE page_type=:pt LIMIT 1");
        $stmt->execute([':pt'=>$pageType]);
        return $stmt->fetch() ?: null;
    }
    public function getGlobal(): ?array {
        return $this->getByPageType('global');
    }
}
