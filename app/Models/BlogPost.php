<?php
declare(strict_types=1);
namespace App\Models;
class BlogPost extends BaseModel {
    protected string $table = 'blog_posts';

    // Public safe read — only published and visible
    public function getPublished(int $limit = 0): array {
        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug,
                   mf.file_url as featured_image_url
            FROM blog_posts p
            LEFT JOIN blog_categories c ON p.category_id = c.id
            LEFT JOIN media mf ON p.featured_image_id = mf.id
            WHERE p.status='published' AND p.is_visible=1
            ORDER BY p.published_date_iso DESC
        ";
        if ($limit > 0) $sql .= " LIMIT " . intval($limit);
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, c.slug as category_slug,
                   mf.file_url as featured_image_url, mf.file_path as featured_image_path,
                   mo.file_url as og_image_url
            FROM blog_posts p
            LEFT JOIN blog_categories c ON p.category_id = c.id
            LEFT JOIN media mf ON p.featured_image_id = mf.id
            LEFT JOIN media mo ON p.og_image_id = mo.id
            WHERE p.slug=:slug AND p.status='published' AND p.is_visible=1
            LIMIT 1
        ");
        $stmt->execute([':slug'=>$slug]);
        $row = $stmt->fetch();
        if (!$row) return null;

        // Fetch tags
        $tagStmt = $this->db->prepare("
            SELECT t.name, t.slug FROM blog_tags t
            JOIN blog_post_tags pt ON t.id = pt.tag_id
            WHERE pt.post_id=:pid ORDER BY t.name ASC
        ");
        $tagStmt->execute([':pid'=>$row['id']]);
        $row['tags'] = $tagStmt->fetchAll();
        $row['tags_names'] = array_column($row['tags'], 'name');

        return $row;
    }

    public function getByOriginalId(string $originalId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM blog_posts WHERE original_id=:oid LIMIT 1");
        $stmt->execute([':oid'=>$originalId]);
        return $stmt->fetch() ?: null;
    }

    // Related posts logic: same category +2, shared tags +1 per tag, recency, exclude current
    public function getRelated(int $currentId, int $limit = 3): array {
        // Get current post category and tags
        $current = $this->findById($currentId);
        if (!$current) return [];

        $currentCategoryId = $current['category_id'];

        $tagStmt = $this->db->prepare("SELECT tag_id FROM blog_post_tags WHERE post_id=:pid");
        $tagStmt->execute([':pid'=>$currentId]);
        $currentTagIds = $tagStmt->fetchAll(\PDO::FETCH_COLUMN);

        // If no category and no tags, fallback to most recent
        if (!$currentCategoryId && empty($currentTagIds)) {
            $stmt = $this->db->prepare("
                SELECT * FROM blog_posts 
                WHERE id != :cid AND status='published' AND is_visible=1
                ORDER BY published_date_iso DESC LIMIT :lim
            ");
            $stmt->bindValue(':cid', $currentId, \PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        // Build scoring query
        // We will do in PHP for portability: get all other published posts, score, sort
        $allStmt = $this->db->prepare("
            SELECT p.*, c.name as category_name FROM blog_posts p
            LEFT JOIN blog_categories c ON p.category_id = c.id
            WHERE p.id != :cid AND p.status='published' AND p.is_visible=1
            ORDER BY p.published_date_iso DESC
        ");
        $allStmt->execute([':cid'=>$currentId]);
        $allPosts = $allStmt->fetchAll();

        // Get tags for all posts in one query
        $allIds = array_column($allPosts, 'id');
        $tagsByPost = [];
        if (!empty($allIds)) {
            $in = implode(',', array_map('intval', $allIds));
            $tagsStmt = $this->db->query("SELECT post_id, tag_id FROM blog_post_tags WHERE post_id IN ($in)");
            foreach ($tagsStmt->fetchAll() as $row) {
                $tagsByPost[$row['post_id']][] = $row['tag_id'];
            }
        }

        $scored = [];
        foreach ($allPosts as $post) {
            $score = 0;
            if ($currentCategoryId && $post['category_id'] == $currentCategoryId) $score += 2;
            $otherTags = $tagsByPost[$post['id']] ?? [];
            $shared = count(array_intersect($currentTagIds, $otherTags));
            $score += $shared;
            $scored[] = ['score'=>$score, 'date'=>strtotime($post['published_date_iso'] ?? $post['published_date']), 'post'=>$post];
        }

        usort($scored, function($a,$b){
            if ($a['score'] !== $b['score']) return $b['score'] <=> $a['score'];
            return $b['date'] <=> $a['date'];
        });

        // If all scores 0, fallback to most recent
        $allZero = true;
        foreach ($scored as $s) if ($s['score'] > 0) $allZero = false;
        if ($allZero) {
            return array_slice(array_column($scored, 'post'), 0, $limit);
        }

        return array_slice(array_column($scored, 'post'), 0, $limit);
    }

    // For admin (includes drafts) — not public
    public function getAllForAdmin(): array {
        $stmt = $this->db->query("SELECT * FROM blog_posts ORDER BY published_date_iso DESC");
        return $stmt->fetchAll();
    }
}
