<?php
declare(strict_types=1);
namespace App\Models;
class ProjectFeature extends BaseModel {
    protected string $table = 'project_features';
    public function getByProjectId(int $projectId): array {
        $stmt = $this->db->prepare("SELECT * FROM project_features WHERE project_id=:pid ORDER BY order_index ASC");
        $stmt->execute([':pid'=>$projectId]);
        return $stmt->fetchAll();
    }
}
