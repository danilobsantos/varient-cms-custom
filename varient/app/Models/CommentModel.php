<?php

namespace App\Models;

class CommentModel extends BaseModel
{
    protected $table = 'comments';
    protected $allowedFields = ['parent_id', 'post_id', 'user_id', 'email', 'name', 'comment', 'ip_address', 'like_count', 'status', 'created_at'];
    protected $createdField = 'created_at';

    protected $validationRules = [
        'post_id' => [
            'label' => 'trans:post',
            'rules' => 'required|integer',
        ],
        'comment' => [
            'label' => 'trans:comment',
            'rules' => 'required|max_length[5000]',
        ],
    ];

    /**
     * Base query with user joins
     */
    private function buildQuery()
    {
        return $this->select('comments.*, 
        users.username AS user_username, users.slug AS user_slug, users.avatar AS user_avatar, users.storage_avatar AS user_storage_avatar, users.role_id,
        r.badge_id AS user_badge_id,
        (SELECT plan_id FROM user_subscriptions us WHERE us.user_id = users.id AND us.status = "active" ORDER BY us.id DESC LIMIT 1) AS user_plan_id')
            ->join('users', 'users.id = comments.user_id', 'left')
            ->join('roles r', 'r.id = users.role_id', 'left');
    }

    /**
     * Fetches full comment tree (Main + Replies) with ownership and like status
     */
    public function getCommentsWithReplies(int $postId, int $limit, int $offset, string $sort, array $ownedCommentIds = [], array $likedCommentIds = []): array
    {
        // Fetch Top Level Comments (Level 0)
        $comments = $this->getPostComments($postId, $limit, $offset, $sort);
        if (empty($comments)) {
            return [];
        }

        // Extract IDs of top-level comments
        $topLevelIds = array_column($comments, 'id');

        // Fetch Level 1 Replies (Direct children) in one query
        $level1Replies = $this->getRepliesByParentIds($topLevelIds);

        // Fetch Level 2 Replies (Grandchildren) in one query
        $level2Replies = [];
        if (!empty($level1Replies)) {
            $level1Ids = array_column($level1Replies, 'id');
            $level2Replies = $this->getRepliesByParentIds($level1Ids);
        }

        // Combine all replies and group them by parent_id
        $allReplies = array_merge($level1Replies, $level2Replies);
        $repliesMap = [];

        foreach ($allReplies as $reply) {
            $reply->is_owner = $this->checkOwnership($reply, $ownedCommentIds);
            $reply->is_liked = in_array((int)$reply->id, $likedCommentIds);
            $reply->replies = [];

            // Map the reply to its parent
            $repliesMap[$reply->parent_id][] = $reply;
        }

        // Assemble the tree structure
        foreach ($comments as $comment) {
            $comment->is_owner = $this->checkOwnership($comment, $ownedCommentIds);
            $comment->is_liked = in_array((int)$comment->id, $likedCommentIds);

            // Attach Level 1 replies to Top Level Comment
            $comment->replies = $repliesMap[$comment->id] ?? [];

            // Attach Level 2 replies to Level 1 Replies
            foreach ($comment->replies as $replyL1) {
                $replyL1->replies = $repliesMap[$replyL1->id] ?? [];
            }
        }

        return $comments;
    }

    /**
     * Batch fetch replies for a given array of parent IDs
     */
    public function getRepliesByParentIds(array $parentIds): array
    {
        if (empty($parentIds)) {
            return [];
        }

        return $this->buildQuery()
            ->whereIn('comments.parent_id', $parentIds)
            ->where('comments.status', 1)
            ->orderBy('comments.created_at', 'DESC')
            ->get()->getResult();
    }

    /**
     * Increments or Decrements like count
     */
    public function handleLike(int $commentId, string $type): int
    {
        $comment = $this->find($commentId);
        if (empty($comment)) {
            return 0;
        }

        $currentLikes = (int)$comment->like_count;

        if ($type === 'increment') {
            $newLikes = $currentLikes + 1;
        } else {
            $newLikes = max(0, $currentLikes - 1);
        }

        $this->update($commentId, ['like_count' => $newLikes]);

        return $newLikes;
    }

    /**
     * Retrieves top-level comments with pagination
     */
    public function getPostComments(int $postId, int $limit, int $offset, string $sort = 'newest'): array
    {
        $builder = $this->buildQuery();

        $builder->where('comments.post_id', $postId)
            ->where('comments.parent_id', 0)
            ->where('comments.status', 1);

        switch ($sort) {
            case 'popular':
                // Most liked first, then newest
                $builder->orderBy('comments.like_count', 'DESC')
                    ->orderBy('comments.created_at', 'DESC');
                break;

            case 'oldest':
                // Chronological order (First comment first)
                $builder->orderBy('comments.created_at', 'ASC')
                    ->orderBy('comments.id', 'ASC');
                break;

            case 'newest':
            default:
                // Reverse chronological order (Latest comment first)
                $builder->orderBy('comments.created_at', 'DESC')
                    ->orderBy('comments.id', 'DESC');
                break;
        }

        return $builder->limit($limit, $offset)->get()->getResult();
    }

    /**
     * Retrieves direct child replies
     */
    public function getReplies(int $parentId, int $limit = 100): array
    {
        return $this->buildQuery()
            ->where('comments.parent_id', $parentId)
            ->where('comments.status', 1)
            ->orderBy('comments.created_at', 'ASC')
            ->limit($limit)
            ->get()->getResult();
    }

    /**
     * Retrieves single comment by ID
     */
    public function getCommentById(int $commentId)
    {
        return $this->buildQuery()
            ->where('comments.id', $commentId)
            ->get()->getRow();
    }

    /**
     * Retrieves approved comments count
     */
    public function getPostCommentCounts(int $postId): object
    {
        $query = $this->select("COUNT(id) as total_count, SUM(CASE WHEN parent_id = 0 THEN 1 ELSE 0 END) as root_count")
            ->where('post_id', $postId)
            ->where('status', 1)
            ->get()
            ->getRow();

        // If no result, return default object
        if (!$query) {
            return (object)['total_count' => 0, 'root_count' => 0];
        }

        $query->total_count = (int)$query->total_count;
        $query->root_count = (int)$query->root_count;

        return $query;
    }

    /**
     * Counts replies for a comment
     */
    public function countReplies(int $parentId): int
    {
        return $this->where('parent_id', $parentId)
            ->where('status', 1)
            ->countAllResults();
    }

    /**
     * Retrieves latest comments by status
     */
    public function findLatestCommentsByStatus(int $status, ?int $limit = null): array
    {
        $builder = $this->select('comments.*, posts.slug AS post_slug')
            ->join('posts', 'posts.id = comments.post_id', 'left')
            ->where('comments.status', $status)
            ->orderBy('comments.created_at', 'DESC');

        if ($limit !== null) {
            $builder->limit(intval($limit));
        }

        return $builder->get()->getResult();
    }

    /**
     * Inserts new comment
     */
    public function addComment(array $data): int|bool
    {
        if ($this->insert($data) === false) {
            return false;
        }

        $insertId = $this->insertID();

        // Update post comment count
        if (!empty($data['post_id']) && !empty($data['status'])) {
            $this->updatePostTotalCommentCount((int)$data['post_id']);
        }

        return $insertId;
    }

    /**
     * Sets status of comments to 1 (Approved)
     */
    public function approveComments(array $commentIds): bool
    {
        if (empty($commentIds)) {
            return true;
        }

        // Retrieve Post IDs to update counts later
        $postIds = $this->whereIn('id', $commentIds)->findColumn('post_id');

        // Approve comments
        $result = $this->whereIn('id', $commentIds)
            ->set(['status' => 1])
            ->update();

        // If update is successful, recalculate counts for affected posts
        if ($result && !empty($postIds)) {
            $this->updateBatchPostCommentCounts($postIds);
        }

        return $result;
    }

    /**
     * Filters and Paginates  all comments
     */
    public function findAllPaginated(array $filters, int $perPage): array
    {
        $totalCount = $this->countAllResults();

        $builder = $this->select('comments.*, p.title AS post_title, p.slug AS post_slug, p.post_url AS post_url, p.lang_id AS post_lang_id')
            ->join('posts p', 'p.id = comments.post_id', 'left');

        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== 'all') {
            $builder->where('comments.status', $filters['status'] === 'pending' ? 0 : 1);
        }

        return $builder->orderBy('comments.created_at', 'DESC')
            ->customPaginate($perPage, $totalCount);
    }

    /**
     * Check if user (Logged-in or Guest via Session) owns the comment
     */
    private function checkOwnership($comment, array $ownedCommentIds = [])
    {
        // Auth User Check
        if (authCheck() && (int)$comment->user_id === (int)user()->id) {
            return true;
        }

        // Guest Check
        if (!authCheck() && !empty($ownedCommentIds) && in_array((int)$comment->id, $ownedCommentIds)) {
            return true;
        }

        return false;
    }

    /**
     * Updates comment count of a post
     */
    public function updatePostTotalCommentCount(int $postId): void
    {
        $count = $this->where('post_id', $postId)->where('status', 1)->countAllResults();

        if ($count >= 0) {
            model('PostModel')->update($postId, ['comment_count' => $count]);
        }
    }

    /**
     * Updates comment counts for multiple posts
     */
    protected function updateBatchPostCommentCounts(array $postIds): void
    {
        // Remove duplicate post IDs to avoid unnecessary queries
        $uniquePostIds = array_unique($postIds);

        foreach ($uniquePostIds as $postId) {
            if (!empty($postId)) {
                $this->updatePostTotalCommentCount((int)$postId);
            }
        }
    }

    /**
     * Retrieves aggregated comment statistics (total, pending, approved)
     */
    public function getCommentStatistics(): array
    {
        $stats = $this->select('COUNT(*) as total, SUM(status = 0) as pending, SUM(status = 1) as approved')
            ->asArray()
            ->first();

        // Ensure we always return integers, even if the table is empty
        return [
            'total'    => (int)($stats['total'] ?? 0),
            'pending'  => (int)($stats['pending'] ?? 0),
            'approved' => (int)($stats['approved'] ?? 0),
        ];
    }

    /**
     * Recursively deletes comments and replies
     */
    public function deleteCommentsAndReplies(array $commentIds): bool
    {
        if (empty($commentIds)) {
            return true;
        }

        // Retrieve the Post IDs associated with these comments BEFORE deletion
        $postIds = $this->whereIn('id', $commentIds)->findColumn('post_id');

        $this->db->transStart();

        // Delete replies first
        $this->whereIn('parent_id', $commentIds)->delete();
        // Delete the comments themselves
        $this->whereIn('id', $commentIds)->delete();

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return false;
        }

        // Update comment counts for the affected posts
        if (!empty($postIds)) {
            $this->updateBatchPostCommentCounts($postIds);
        }

        return true;
    }
}