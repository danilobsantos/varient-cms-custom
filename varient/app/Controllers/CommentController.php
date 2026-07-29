<?php

namespace App\Controllers;

use App\Services\CaptchaService;
use CodeIgniter\API\ResponseTrait;

class CommentController extends BaseController
{
    use ResponseTrait;

    protected $commentModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        if (!$this->request->isAJAX()) {
            return jsonResponse(['status' => 400, 'message' => 'Invalid Request!']);
        }

        $this->commentModel = model('CommentModel');
    }

    /**
     * Load Comments
     */
    public function loadComments()
    {
        $postId = (int)inputPost('post_id');

        $limit = (int)(inputPost('limit') ?? 5);
        $offset = (int)(inputPost('offset') ?? 0);
        $sort = inputPost('sort') ?? 'newest';

        // Retrieve session data for ownership and likes
        $ownedCommentIds = getSession('owned_comment_ids') ?? [];
        $likedCommentIds = getSession('liked_comment_ids') ?? [];

        if (!$postId) {
            return jsonResponse(false);
        }

        // Fetch Data - Passing both session arrays to model
        $comments = $this->commentModel->getCommentsWithReplies((int)$postId, (int)$limit, (int)$offset, $sort, $ownedCommentIds, $likedCommentIds);
        $total = $this->commentModel->getPostCommentCounts((int)$postId)->root_count;

        // Render HTML
        $html = '';
        foreach ($comments as $comment) {
            $html .= loadCommonView('comments/_comment', ['comment' => $comment, 'level' => 1]);
        }

        return jsonResponse([
            'status' => 'success',
            'html'   => $html,
            'total'  => $total
        ]);
    }

    /**
     * Add Comment or Reply
     */
    public function addComment()
    {
        // Validation rules
        $rules = [
            'post_id' => 'required|integer',
            'comment' => 'required|min_length[2]|max_length[5000]',
        ];

        // Validate guest input
        if (!authCheck()) {
            $rules['name'] = 'required|min_length[2]|max_length[50]';
            $rules['email'] = 'required|valid_email|max_length[100]';
        }

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        if (!authCheck()) {
            $captchaService = new CaptchaService($this->config);
            if (!$captchaService->verifyJs()) {
                return jsonResponse([
                    'status'  => 'error',
                    'message' => trans("bot_verification_failed")
                ]);
            }
        }

        $postId = (int)inputPost('post_id');
        $parentId = (int)(inputPost('parent_id') ?? 0);

        // Check content access
        if (!$this->hasContentAccess($postId)) {
            return jsonResponse([
                'status'  => 'error',
                'message' => trans("invalid_attempt")
            ]);
        }

        // Check recursion depth (max 3 levels)
        $parentLevel = 0;
        if ($parentId > 0) {
            $parent = $this->commentModel->find($parentId);
            if ($parent) {
                $parentLevel = 1;
                if ($parent->parent_id > 0) {
                    $parentLevel = 2;
                    // Check if parent is already a child of another reply
                    $grandParent = $this->commentModel->find($parent->parent_id);
                    if ($grandParent && $grandParent->parent_id > 0) {
                        return $this->failForbidden('Maximum reply depth reached.');
                    }
                }
            }
        }

        // Determine status based on approval settings
        $status = 1;
        if ($this->config->comment_approval_system == 1 && !hasPermission('comments')) {
            $status = 0;
        }

        // Prepare data
        $data = [
            'post_id'    => $postId,
            'parent_id'  => $parentId,
            'comment'    => inputPost('comment'),
            'ip_address' => $this->request->getIPAddress(),
            'status'     => $status,
            'created_at' => dateTimeNow()
        ];

        if (authCheck()) {
            $user = user();
            $data['user_id'] = $user->id;
            $data['name'] = $user->username;
            $data['email'] = $user->email;
        } else {
            $data['user_id'] = 0;
            $data['name'] = inputPost('name');
            $data['email'] = inputPost('email');
        }

        // Security: Check email blacklisted
        $email = $data['email'] ?? '';
        if (model('EmailBlacklistModel')->isBlacklisted($email)) {
            return jsonResponse([
                'status'  => 'error',
                'message' => trans("msg_error_email_blacklisted")
            ]);
        }

        // Clean external links
        $data['comment'] = cleanExternalLinks($data['comment'], 'public');
        // Clear tags
        $data['name'] = strip_tags($data['name']);
        $data['email'] = strip_tags($data['email']);

        // Insert comment
        $newId = $this->commentModel->addComment($data);

        if (!empty($newId)) {
            // If user is guest, save the new comment ID to session
            if (!authCheck()) {
                $ownedIds = getSession('owned_comment_ids') ?? [];
                $ownedIds[] = $newId;
                setSession('owned_comment_ids', $ownedIds);
            }

            // Pending approval - Return message only
            if ($status == 0) {
                return jsonResponse([
                    'status'         => 'success',
                    'comment_status' => 'pending',
                    'message'        => trans("msg_comment_sent_successfully")
                ]);
            }

            // Approved - Return HTML for display
            $newComment = $this->commentModel->getCommentById($newId);

            // Set properties for immediate display
            $newComment->replies = [];
            $newComment->is_owner = true;
            $newComment->is_liked = false;

            $newLevel = $parentLevel + 1;
            $html = loadCommonView('comments/_comment', ['comment' => $newComment, 'level' => $newLevel]);

            return jsonResponse([
                'status'         => 'success',
                'comment_status' => 'approved',
                'html'           => $html,
                'message'        => trans("comment_success")
            ]);
        }

        return $this->failServerError('Could not save comment');
    }

    /**
     * Delete Comment
     */
    public function deleteComment()
    {
        $commentId = (int)inputPost('comment_id');

        $comment = $this->commentModel->find($commentId);
        if (!$comment) {
            return $this->failNotFound('Comment not found');
        }

        // Check strict ownership
        $isOwner = $this->isCommentOwner($comment);

        // Allow Admins to override ownership
        if (hasPermission('comments')) {
            $isOwner = true;
        }

        if (!$isOwner) {
            return $this->failForbidden('You are not authorized to delete this comment.');
        }

        if ($this->commentModel->deleteCommentsAndReplies([(int)$commentId])) {
            return $this->respondDeleted(['status' => 'success']);
        }

        return $this->failServerError('Deletion failed');
    }

    /**
     * Toggle Like
     */
    public function likeComment()
    {
        $commentId = (int)inputPost('comment_id');
        if (!$commentId) {
            return $this->fail('Invalid comment ID');
        }

        // Find the comment
        $comment = $this->commentModel->find($commentId);
        if (!$comment) {
            return $this->failNotFound('Comment not found');
        }

        // Check content access
        if (!$this->hasContentAccess($comment->post_id)) {
            return $this->fail(trans("invalid_attempt"));
        }

        // Prevent self-liking (Ownership Check)
        if ($this->isCommentOwner($comment)) {
            return jsonResponse(['status' => 'error', 'message' => 'Cannot like own comment']);
        }

        // Retrieve liked IDs from session
        $likedIds = getSession('liked_comment_ids') ?? [];

        $action = 'increment';

        // Check if already liked
        if (in_array((int)$commentId, $likedIds)) {
            // Already liked -> Undo like
            $action = 'decrement';
            // Remove ID from array
            $likedIds = array_values(array_diff($likedIds, [(int)$commentId]));
        } else {
            // Not liked -> Add like
            $likedIds[] = (int)$commentId;
        }

        // Save updated list back to session
        setSession('liked_comment_ids', $likedIds);

        // Update Database count
        $newLikeCount = $this->commentModel->handleLike((int)$commentId, $action);

        return jsonResponse([
            'status' => 'success',
            'likes'  => $newLikeCount
        ]);
    }

    /**
     * Helper to check if the current user (Logged in or Guest) owns a specific comment
     */
    private function isCommentOwner($comment): bool
    {
        // Logged-in User Check
        if (authCheck()) {
            return (int)$comment->user_id === (int)user()->id;
        }

        // Guest Check (via Session)
        $ownedCommentIds = getSession('owned_comment_ids') ?? [];
        return in_array((int)$comment->id, $ownedCommentIds);
    }

    /**
     * Helper to check if the current user has content access
     */
    private function hasContentAccess($postId): bool
    {
        $post = model('PostModel')->find($postId);
        if (empty($post)) {
            return false;
        }

        $contentAccessStatus = getContentAccessStatus($post);

        if (!$contentAccessStatus->hasAccess) {
            return false;
        }

        return true;
    }

}