<?php
declare(strict_types=1);
namespace App\Models;
class ProjectTechnology extends BaseModel {
    protected string $table = 'project_technologies';
    public function getByProjectId(int $projectId): array {
        $stmt = $this->db->prepare("SELECT * FROM project_technologies WHERE project_id=:pid ORDER BY technology ASC");
        $stmt->execute([':pid'=>$projectId]);
        return $stmt->fetchAll();
    }
}
