<?php

namespace App\Models;

class ReadingListModel extends BaseModel
{
    protected $table = 'reading_lists';
    protected $allowedFields = ['post_id', 'user_id', 'created_at'];
    protected $createdField = 'created_at';

    /**
     * Toggles a post in the user's reading list
     */
    public function togglePost(int $postId, int $userId): string
    {
        $existing = $this->where('post_id', $postId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $this->delete($existing->id);
            return 'removed';
        }

        return $this->insert([
            'post_id' => $postId,
            'user_id' => $userId
        ]);

        return 'added';
    }

    /**
     * Checks if a post is in the user's reading list
     */
    public function isPostInList(int $postId, int $userId): bool
    {
        $result = $this->select('id')
            ->where('post_id', $postId)
            ->where('user_id', $userId)
            ->first();

        return !empty($result);
    }
}