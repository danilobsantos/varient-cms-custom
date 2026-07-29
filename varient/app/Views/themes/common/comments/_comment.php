<?php
$level = isset($level) ? $level : 1;

// Safe Output
$safeName = esc($comment->name ?? $comment->user_username ?? 'Guest');
$safeContent = esc($comment->comment);
$dateDisplay = timeAgo($comment->created_at);

// Avatar Logic
$avatarUrl = !empty($comment->user_avatar)
        ? base_url($comment->user_avatar)
        : (!empty($comment->avatar) ? $comment->avatar : base_url(AVATAR_GUEST));

// Ownership & State
$isOwner = $comment->is_owner ?? false;
$isLiked = $comment->is_liked ?? false;

// UI Helpers
$likeClass = $isLiked ? 'liked' : '';
$likeIcon = $isLiked ? 'vri-heart-fill' : 'vri-heart';
$likeAttr = $isOwner ? 'disabled style="cursor:default; opacity:0.5"' : '';
$ownerBadge = $isOwner ? '<span class="owner-badge">' . trans("you", true) . '</span>' : '';

// Actions
$deleteBtn = $isOwner ? '<button class="footer-action delete-action btn-delete" data-id="' . $comment->id . '"><span class="separator">•</span> <i class="vri vri-trash"></i><span>' . trans("delete", true) . '</span></button>' : '';

$replyBtn = '';
$replyForm = '';

if ($level < 3) {
    $replyBtn = '<span class="separator">•</span><button class="footer-action btn-reply" data-id="' . $comment->id . '"><i class="vri vri-reply"></i><span>' . trans("reply", true) . '</span></button>';

    $guestInputs = (!function_exists('authCheck') || !authCheck()) ? '
        <div class="inline-guest-inputs">
            <input type="text" class="input-minimal" placeholder="' . trans('name', 'attr') . '" required>
            <input type="email" class="input-minimal" placeholder="' . trans('email', 'attr') . '" required>
        </div>' : '';

    $replyForm = '
    <div class="inline-reply-wrapper" id="reply-form-wrapper-' . $comment->id . '">
        <form class="reply-form-inline" novalidate data-parent="' . $comment->id . '">
            <div class="editor-container">
                <textarea class="editor-textarea"  placeholder="' . trans('write_a_reply', 'attr') . '" required></textarea>
                ' . $guestInputs . '
            </div>
            <div class="inline-footer">
                <div class="btn-group-right">
                    <button type="button" class="btn btn-sm btn-link text-muted text-decoration-none btn-cancel-reply" data-target="' . $comment->id . '">' . trans("cancel", true) . '</button>
                    <button type="submit" class="btn btn-sm btn-custom btn-post-comment">' . trans("post_reply", true) . '</button>
                </div>
            </div>
        </form>
    </div>';
}

// Recursive Replies
$childrenHtml = '';
if (!empty($comment->replies)) {
    $limit = 2;
    $visible = array_slice($comment->replies, 0, $limit);
    $hidden = array_slice($comment->replies, $limit);

    foreach ($visible as $reply) {
        $childrenHtml .= loadCommonView('comments/_comment', ['comment' => $reply, 'level' => $level + 1]);
    }

    if (!empty($hidden)) {
        $childrenHtml .= '<div class="hidden-reply" style="display:none;">';
        foreach ($hidden as $reply) {
            $childrenHtml .= loadCommonView('comments/_comment', ['comment' => $reply, 'level' => $level + 1]);
        }
        $childrenHtml .= '</div>';
        $childrenHtml .= '<button class="btn-show-replies"><i class="vri vri-reply" style="transform: rotate(180deg);"></i>' . trans("view_more_replies", true) . '</button>';
    }
}

$threadContainer = !empty($childrenHtml) ? '<div class="thread-line-container"><div class="replies-list">' . $childrenHtml . '</div></div>' : '';
?>

<div class="comment-item" id="comment-<?= $comment->id ?>" data-level="<?= $level ?>">
    <div class="avatar-container">
        <?php if (!empty($comment->user_slug)): ?>
            <a href="<?= generateProfileURL($comment->user_slug); ?>">
                <img src="<?= $avatarUrl ?>" class="avatar-img" width="38" height="38" alt="<?= $safeName ?>">
            </a>
        <?php else: ?>
            <img src="<?= $avatarUrl ?>" class="avatar-img" width="38" height="38" alt="<?= $safeName ?>">
        <?php endif; ?>
    </div>
    <div class="comment-body">
        <div class="comment-meta">
            <?php if (!empty($comment->user_slug)): ?>
                <a href="<?= generateProfileURL($comment->user_slug); ?>">
                    <span class="author-name"><?= $safeName ?></span>
                </a>
            <?php else: ?>
                <span class="author-name"><?= $safeName ?></span>
            <?php endif; ?>
            <?= $ownerBadge ?>

            <?php if (!empty($comment->user_plan_id)): ?>
                <?= renderUserBadge(['plan_id' => $comment->user_plan_id, 'lang_id' => $activeLang->id, 'size' => 'sm']); ?>
            <?php else: ?>
                <?= !empty($comment->user_badge_id) ? renderUserBadge(['badge_id' => $comment->user_badge_id, 'lang_id' => $activeLang->id, 'size' => 'sm']) : ''; ?>
            <?php endif; ?>

            <span class="timestamp"><?= $dateDisplay ?></span>
        </div>
        <div class="comment-content"><?= $safeContent ?></div>
        <div class="comment-footer">
            <button class="footer-action btn-like <?= $likeClass ?>" data-id="<?= $comment->id ?>" <?= $likeAttr ?>>
                <i class="vri <?= $likeIcon ?>"></i><span><?= $comment->like_count ?? 0; ?></span>
            </button>
            <?= $replyBtn ?>
            <?= $deleteBtn ?>
        </div>
        <?= $replyForm ?>
        <?= $threadContainer ?>
    </div>
</div>