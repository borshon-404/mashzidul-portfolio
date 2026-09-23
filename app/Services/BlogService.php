<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\BlogPost;

/**
 * Blog Service — preserves current related logic
 * Milestone 3: Foundation
 */
class BlogService
{
    private BlogPost $blogPostModel;

    public function __construct()
    {
        $this->blogPostModel = new BlogPost();
    }

    public function getPublishedPosts(int $limit = 0): array
    {
        return $this->blogPostModel->getPublished($limit);
    }

    public function getPostBySlug(string $slug): ?array
    {
        return $this->blogPostModel->getBySlug($slug);
    }

    public function getRelatedPosts(int $currentId, int $limit = 3): array
    {
        return $this->blogPostModel->getRelated($currentId, $limit);
    }

    public function getLatestPosts(int $count = 3): array
    {
        return $this->blogPostModel->getPublished($count);
    }

    // For future frontend integration, preserve current behavior
    public function formatDateLong(string $date): string
    {
        try {
            $dt = new \DateTime($date);
            return $dt->format('F d, Y');
        } catch (\Exception $e) {
            return $date;
        }
    }

    public function formatDateShort(string $date): string
    {
        try {
            $dt = new \DateTime($date);
            return $dt->format('M d, Y');
        } catch (\Exception $e) {
            return $date;
        }
    }

    public function extractToc(string $html): array
    {
        $pattern = '/<h([23])\s+id="([^"]+)"[^>]*>(.*?)<\/h\1>/i';
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
        $toc = [];
        foreach ($matches as $m) {
            $toc[] = [
                'level' => (int)$m[1],
                'id' => $m[2],
                'title' => strip_tags($m[3]),
            ];
        }
        return $toc;
    }
}
