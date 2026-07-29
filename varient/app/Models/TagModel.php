<?php

namespace App\Models;

use App\Entities\Tag;

class TagModel extends BaseModel
{
    protected $table = 'tags';
    protected $allowedFields = ['tag', 'tag_slug', 'lang_id'];
    protected $returnType = Tag::class;
    protected $cacheGroup = 'app';

    /**
     * Gets the ID of a tag by name and language, creating it if it doesn't exist
     */
    public function getOrCreate(Tag $tag): int|false
    {
        $row = $this->select('id')
            ->where('tag_slug', $tag->tag_slug)->where('lang_id', $tag->lang_id)
            ->first();

        if ($row) {
            return (int)$row->id;
        }

        try {
            $id = $this->insert($tag);
            return $id ? (int)$id : false;
        } catch (\Throwable $e) {
            // Race condition fallback
            $row = $this->select('id')
                ->where('tag_slug', $tag->tag_slug)
                ->where('lang_id', $tag->lang_id)
                ->first();

            return $row ? (int)$row->id : false;
        }
    }

    /**
     * Synchronizes the tags of a post based on a comma-separated list of tag names
     */
    public function syncTagsForPost(?object $post, ?string $tagNamesCsv): void
    {
        if (empty($post)) {
            return;
        }

        $postId = $post->id;
        $langId = $post->lang_id;

        $tagNames = empty($tagNamesCsv) ? [] : array_unique(array_filter(array_map('trim', explode(',', $tagNamesCsv))));

        $newTagIds = [];

        foreach ($tagNames as $name) {
            try {
                $tag = Tag::fromName($name, $langId);
                $tagId = $this->getOrCreate($tag);
                if ($tagId !== false) {
                    $newTagIds[] = $tagId;
                }
            } catch (\InvalidArgumentException) {
                continue;
            }
        }

        $currentTagIds = $this->db->table('post_tags')
            ->select('tag_id')
            ->where('post_id', $postId)
            ->get()
            ->getResultArray();

        $currentTagIds = array_column($currentTagIds, 'tag_id');

        $idsToDelete = array_diff($currentTagIds, $newTagIds);
        $idsToInsert = array_diff($newTagIds, $currentTagIds);

        if (!$idsToDelete && !$idsToInsert) {
            return;
        }

        $this->db->transStart();

        if ($idsToDelete) {
            $this->db->table('post_tags')
                ->where('post_id', $postId)
                ->whereIn('tag_id', $idsToDelete)
                ->delete();
        }

        if ($idsToInsert) {
            $this->db->table('post_tags')->insertBatch(
                array_map(fn($id) => ['post_id' => $postId, 'tag_id' => $id, 'lang_id' => $langId], $idsToInsert)
            );
        }

        $this->db->transComplete();
    }

    /**
     * Returns the most popular tags by post count for a specific language
     */
    public function getPopularTags(int $langId, int $limit = 20): array
    {
        $topTags = $this->db->table('post_tags')
            ->select('tag_id, COUNT(post_id) as popularity')
            ->where('lang_id', $langId)
            ->groupBy('tag_id')
            ->orderBy('popularity', 'DESC')
            ->limit($limit)
            ->get()->getResult();

        if (empty($topTags)) {
            return [];
        }

        $tagIds = array_column($topTags, 'tag_id');
        $popularityMap = array_column($topTags, 'popularity', 'tag_id');

        $tags = $this->builder()
            ->select('id, tag, tag_slug')
            ->whereIn('id', $tagIds)
            ->get()->getResult();

        foreach ($tags as $tag) {
            $tag->popularity = $popularityMap[$tag->id] ?? 0;
        }

        usort($tags, function ($a, $b) {
            return $b->popularity <=> $a->popularity;
        });

        return $tags;
    }

    /**
     * Returns a list of tag names matching the query
     */
    public function getTagSuggestions(string $query, ?int $langId = null, int $limit = 20): array
    {
        $term = cleanStr($query);
        if (mb_strlen($term) < 1) {
            return [];
        }

        $this->select('tag')->like('tag', $term, 'after');
        if (!empty($langId)) {
            $this->where('lang_id', (int)$langId);
        }

        $result = $this->distinct()->limit($limit)->findColumn('tag');

        return $result ?? [];
    }

    /**
     * Finds a tag by slug
     */
    public function findBySlug(?string $slug, int $langId): ?object
    {
        return $this->where('tag_slug', cleanStr($slug))
            ->where('lang_id', $langId)
            ->first();
    }

    /**
     * Finds all tags associated with a specific post ID
     */
    public function findAllByPostId(int $postId): array
    {
        if (empty($postId)) {
            return [];
        }

        return $this->select('tags.*')
            ->join('post_tags', 'post_tags.tag_id = tags.id')
            ->where('post_tags.post_id', $postId)
            ->findAll();
    }

    /**
     * Finds, filters, and paginates all tags
     */
    public function findAllPaginated(array $filters, int $perPage): array
    {
        $builder = $this->select('tags.*, (SELECT COUNT(post_tags.id) FROM post_tags WHERE post_tags.tag_id = tags.id) AS count');

        if (!empty($filters['lang_id'])) {
            $builder->where('tags.lang_id', $filters['lang_id']);
        }
        if (!empty($filters['q'])) {
            $builder->like('tags.tag', $filters['q']);
        }

        return $builder->orderBy('tags.id DESC')
            ->paginate($perPage);
    }

    /**
     * Deletes post tags
     */
    public function deletePostTags(int $postId): bool
    {
        return $this->db->table('post_tags')
            ->where('post_id', $postId)
            ->delete();
    }

    /**
     * Deletes multiple tags and all their associations
     */
    public function deleteTags(array $tagIds): bool
    {
        if (empty($tagIds)) {
            return false;
        }

        $this->db->transStart();

        $this->db->table('post_tags')->whereIn('tag_id', $tagIds)->delete();

        $this->delete($tagIds);

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}