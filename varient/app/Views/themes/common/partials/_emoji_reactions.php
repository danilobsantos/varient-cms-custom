<?php
$modalTarget = '';
$triggerClass = '';

if (!$hasAccess && !empty($restrictionType)) {
    $triggerClass = ' trigger-paywall';
    $modalTarget = 'data-bs-toggle="modal" data-bs-target="#' . esc($restrictionType, 'attr') . 'ActionModal"';
}
?>
<h4 class="reaction-wrapper-title"><?= trans("whats_your_reaction"); ?></h4>
<div class="reaction-wrapper">
    <div class="reaction-grid" id="reactionContainer" data-post-id="<?= isset($post) ? esc($post->id) : 0; ?>">
        <?php
        $reactions = (object)getAppDefault('emojiReactions');
        foreach ($reactions as $reaction):
            $reaction = (object)$reaction;
            $imgUrl = base_url("assets/media/emoji/{$reaction->key}.webp");
            $numVotes = $postReactions[$reaction->key] ?? 0;
            $isReacted = hasUserReacted($reaction->key, $post->id);

            $activeClass = $isReacted ? ' active' : '';
            $finalClass = "reaction-btn" . $triggerClass . $activeClass;
            ?>
            <div class="<?= $finalClass; ?>" <?= $modalTarget; ?> data-reaction="<?= esc($reaction->key, 'attr'); ?>" style="<?= esc($reaction->style, 'attr'); ?>">
                <img src="<?= $imgUrl; ?>" alt="<?= trans($reaction->key, 'attr'); ?>" class="reaction-img">
                <span class="reaction-label"><?= trans($reaction->key, 'attr'); ?></span>
                <span class="reaction-count"><?= $numVotes; ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>