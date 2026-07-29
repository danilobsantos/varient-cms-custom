<?php

namespace App\Models;

class ReactionModel extends BaseModel
{
    protected $table = 'reactions';
    protected $allowedFields = ['post_id', 'reaction', 'total'];

    /**
     * Updates reaction total safely using atomic operations
     */
    public function updateReactionTotal(int $reactionId, string $operation): bool
    {
        if (!in_array($operation, ['increase', 'decrease'], true)) {
            return false;
        }

        if ($operation === 'increase') {
            return $this->where('id', $reactionId)->increment('total');
        }

        if ($operation === 'decrease') {
            return $this->where('id', $reactionId)->where('total >', 0)->decrement('total');
        }

        return false;
    }

    /**
     * Finds by reaction key and post ID
     */
    public function findReactionByKey(string $reaction, int $postId): ?object
    {
        return $this->where('reaction', cleanStr($reaction))
            ->where('post_id', $postId)
            ->first();
    }

    /**
     * Finds all reactions by post ID
     */
    public function findAllByPostId(int $postId, bool $map = false): array
    {
        $rows = $this->where('post_id', $postId)->findAll();
        if ($map === false) {
            return $rows;
        }

        $reactionsConfig = getAppDefault('emojiReactions');
        $reactionKeys = array_column($reactionsConfig, 'key');

        $result = array_fill_keys($reactionKeys, 0);

        foreach ($rows as $row) {
            if (isset($result[$row->reaction])) {
                $result[$row->reaction] = (int)$row->total;
            }
        }

        return $result;
    }
}