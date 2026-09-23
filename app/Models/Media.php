<?php
declare(strict_types=1);
namespace App\Models;
class Media extends BaseModel {
    protected string $table = 'media';
    public function getByFilename(string $filename): ?array {
        $stmt = $this->db->prepare("SELECT * FROM media WHERE filename=:fn LIMIT 1");
        $stmt->execute([':fn'=>$filename]);
        return $stmt->fetch() ?: null;
    }
    public function getById(int $id): ?array {
        return $this->findById($id);
    }
    public function getAll(int $limit = 50): array {
        $stmt = $this->db->prepare("SELECT * FROM media ORDER BY created_at DESC LIMIT :lim");
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    // Safe path handling
    public function isSafePath(string $filePath, string $baseDir): bool {
        $realBase = realpath($baseDir);
        $realPath = realpath($filePath);
        if (!$realBase || !$realPath) {
            $realPath = realpath(dirname($filePath));
            if (!$realPath) return false;
        }
        return strpos($realPath, $realBase) === 0;
    }
}
