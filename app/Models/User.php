<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Security;
class User extends BaseModel {
    protected string $table = 'users';
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email=:email AND is_active=1 LIMIT 1");
        $stmt->execute([':email'=>$email]);
        return $stmt->fetch() ?: null;
    }
    public function verifyPassword(string $email, string $password): ?array {
        $user = $this->findByEmail($email);
        if (!$user) return null;
        if (!Security::verifyPassword($password, $user['password_hash'])) return null;
        // Rehash if needed
        if (Security::needsRehash($user['password_hash'])) {
            $newHash = Security::hashPassword($password);
            $upd = $this->db->prepare("UPDATE users SET password_hash=:hash WHERE id=:id");
            $upd->execute([':hash'=>$newHash, ':id'=>$user['id']]);
        }
        return $user;
    }
    public function updateLastLogin(int $id): void {
        $stmt = $this->db->prepare("UPDATE users SET last_login_at=NOW() WHERE id=:id");
        $stmt->execute([':id'=>$id]);
    }
}
