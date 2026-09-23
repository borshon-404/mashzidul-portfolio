<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;
use PDO;

class Project extends BaseModel {
    protected string $table = 'projects';

    public function getAllVisible(): array {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE is_visible=1 ORDER BY order_index ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE slug=:slug AND is_visible=1 LIMIT 1");
        $stmt->execute([':slug'=>$slug]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $techStmt = $this->db->prepare("SELECT technology FROM project_technologies WHERE project_id=:pid ORDER BY technology ASC");
        $techStmt->execute([':pid'=>$row['id']]);
        $row['technologies'] = $techStmt->fetchAll(PDO::FETCH_COLUMN);
        $featStmt = $this->db->prepare("SELECT feature FROM project_features WHERE project_id=:pid ORDER BY order_index ASC");
        $featStmt->execute([':pid'=>$row['id']]);
        $row['features'] = $featStmt->fetchAll(PDO::FETCH_COLUMN);
        return $row;
    }

    public function getFeatured(): array {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE is_visible=1 AND is_featured=1 ORDER BY order_index ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllForAdmin(): array {
        $stmt = $this->db->query("SELECT * FROM projects ORDER BY order_index ASC, id ASC");
        return $stmt->fetchAll();
    }

    public function findByIdAdmin(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE id=:id LIMIT 1");
        $stmt->execute([':id'=>$id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        // Fetch tech and features for admin edit
        $row['technologies'] = $this->getTechnologies($id);
        $row['features'] = $this->getFeatures($id);
        return $row;
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT id FROM projects WHERE slug=:slug AND id!=:excludeId LIMIT 1");
            $stmt->execute([':slug'=>$slug, ':excludeId'=>$excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM projects WHERE slug=:slug LIMIT 1");
            $stmt->execute([':slug'=>$slug]);
        }
        return (bool) $stmt->fetchColumn();
    }

    public function getTechnologies(int $projectId): array {
        $stmt = $this->db->prepare("SELECT * FROM project_technologies WHERE project_id=:pid ORDER BY technology ASC");
        $stmt->execute([':pid'=>$projectId]);
        return $stmt->fetchAll();
    }

    public function getFeatures(int $projectId): array {
        $stmt = $this->db->prepare("SELECT * FROM project_features WHERE project_id=:pid ORDER BY order_index ASC, id ASC");
        $stmt->execute([':pid'=>$projectId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO projects 
            (slug, title, category, duration, cost, role, overview, live_url, github_url, client_name, project_date, art_media_id, is_featured, is_visible, order_index)
            VALUES (:slug, :title, :category, :duration, :cost, :role, :overview, :live_url, :github_url, :client_name, :project_date, :art_media_id, :is_featured, :is_visible, :order_index)
        ");
        $stmt->execute([
            ':slug' => $data['slug'],
            ':title' => $data['title'],
            ':category' => $data['category'] ?? null,
            ':duration' => $data['duration'] ?? null,
            ':cost' => $data['cost'] ?? null,
            ':role' => $data['role'] ?? null,
            ':overview' => $data['overview'] ?? null,
            ':live_url' => $data['live_url'] ?? null,
            ':github_url' => $data['github_url'] ?? null,
            ':client_name' => $data['client_name'] ?? null,
            ':project_date' => $data['project_date'] ?? null,
            ':art_media_id' => $data['art_media_id'] ?? null,
            ':is_featured' => $data['is_featured'] ?? 0,
            ':is_visible' => $data['is_visible'] ?? 1,
            ':order_index' => $data['order_index'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE projects SET
                slug=:slug,
                title=:title,
                category=:category,
                duration=:duration,
                cost=:cost,
                role=:role,
                overview=:overview,
                live_url=:live_url,
                github_url=:github_url,
                client_name=:client_name,
                project_date=:project_date,
                art_media_id=:art_media_id,
                is_featured=:is_featured,
                is_visible=:is_visible,
                order_index=:order_index,
                updated_at=CURRENT_TIMESTAMP
            WHERE id=:id
        ");
        return $stmt->execute([
            ':slug' => $data['slug'],
            ':title' => $data['title'],
            ':category' => $data['category'] ?? null,
            ':duration' => $data['duration'] ?? null,
            ':cost' => $data['cost'] ?? null,
            ':role' => $data['role'] ?? null,
            ':overview' => $data['overview'] ?? null,
            ':live_url' => $data['live_url'] ?? null,
            ':github_url' => $data['github_url'] ?? null,
            ':client_name' => $data['client_name'] ?? null,
            ':project_date' => $data['project_date'] ?? null,
            ':art_media_id' => $data['art_media_id'] ?? null,
            ':is_featured' => $data['is_featured'] ?? 0,
            ':is_visible' => $data['is_visible'] ?? 1,
            ':order_index' => $data['order_index'] ?? 0,
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM projects WHERE id=:id");
        return $stmt->execute([':id'=>$id]);
    }

    // Transactional create with technologies and features
    public function createWithRelations(array $projectData, array $technologies, array $features): int {
        try {
            Database::beginTransaction();
            $projectId = $this->create($projectData);

            // Technologies
            if (!empty($technologies)) {
                $stmtTech = $this->db->prepare("INSERT INTO project_technologies (project_id, technology) VALUES (:project_id, :technology)");
                foreach ($technologies as $tech) {
                    $tech = trim($tech);
                    if ($tech === '') continue;
                    $stmtTech->execute([':project_id'=>$projectId, ':technology'=>$tech]);
                }
            }

            // Features with order
            if (!empty($features)) {
                $stmtFeat = $this->db->prepare("INSERT INTO project_features (project_id, feature, order_index) VALUES (:project_id, :feature, :order_index)");
                $order = 0;
                foreach ($features as $feat) {
                    $feat = trim($feat);
                    if ($feat === '') continue;
                    $stmtFeat->execute([':project_id'=>$projectId, ':feature'=>$feat, ':order_index'=>$order]);
                    $order++;
                }
            }

            Database::commit();
            return $projectId;
        } catch (\Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }

    public function updateWithRelations(int $id, array $projectData, array $technologies, array $features): bool {
        try {
            Database::beginTransaction();

            // Update project
            $this->update($id, $projectData);

            // Replace technologies: delete then insert
            $delTech = $this->db->prepare("DELETE FROM project_technologies WHERE project_id=:pid");
            $delTech->execute([':pid'=>$id]);

            if (!empty($technologies)) {
                $stmtTech = $this->db->prepare("INSERT INTO project_technologies (project_id, technology) VALUES (:project_id, :technology)");
                foreach ($technologies as $tech) {
                    $tech = trim($tech);
                    if ($tech === '') continue;
                    $stmtTech->execute([':project_id'=>$id, ':technology'=>$tech]);
                }
            }

            // Replace features
            $delFeat = $this->db->prepare("DELETE FROM project_features WHERE project_id=:pid");
            $delFeat->execute([':pid'=>$id]);

            if (!empty($features)) {
                $stmtFeat = $this->db->prepare("INSERT INTO project_features (project_id, feature, order_index) VALUES (:project_id, :feature, :order_index)");
                $order = 0;
                foreach ($features as $feat) {
                    $feat = trim($feat);
                    if ($feat === '') continue;
                    $stmtFeat->execute([':project_id'=>$id, ':feature'=>$feat, ':order_index'=>$order]);
                    $order++;
                }
            }

            Database::commit();
            return true;
        } catch (\Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }

    public function countRelations(int $projectId): array {
        $techStmt = $this->db->prepare("SELECT COUNT(*) FROM project_technologies WHERE project_id=:pid");
        $techStmt->execute([':pid'=>$projectId]);
        $featStmt = $this->db->prepare("SELECT COUNT(*) FROM project_features WHERE project_id=:pid");
        $featStmt->execute([':pid'=>$projectId]);
        return [
            'technologies' => (int) $techStmt->fetchColumn(),
            'features' => (int) $featStmt->fetchColumn(),
        ];
    }
}
