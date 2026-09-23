<?php
declare(strict_types=1);
namespace App\Models;
class ContactMessage extends BaseModel {
    protected string $table = 'contact_messages';
    public function getAll(int $limit = 50): array {
        $stmt = $this->db->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT :lim");
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function getUnread(): array {
        $stmt = $this->db->prepare("SELECT * FROM contact_messages WHERE is_read=0 ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function markRead(int $id): bool {
        $stmt = $this->db->prepare("UPDATE contact_messages SET is_read=1, status='read', updated_at=CURRENT_TIMESTAMP WHERE id=:id");
        return $stmt->execute([':id'=>$id]);
    }
}
