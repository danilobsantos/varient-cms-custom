<div class="post-meta">
    <?php if ((int)$config->show_post_author === 1): ?>
        <div class="meta-item">
            <a href="<?= generateProfileURL($postItem->author_slug); ?>" class="a-username"><?= esc(characterLimiter($postItem->author_username, 18, '...')); ?></a>
        </div>
    <?php endif;
    if ((int)$config->show_post_date === 1): ?>
        <div class="meta-item">
            <span><?= formatDateClient($postItem->created_at); ?></span>
        </div>
    <?php endif;
    if ((int)$config->comment_system === 1): ?>
        <div class="meta-item">
            <i class="vr-icon vri-comments"></i>
            <span><?= numberFormatShort($postItem->comment_count); ?></span>
        </div>
    <?php endif;
    if ((int)$config->show_pageviews === 1): ?>
        <div class="meta-item">
            <i class="vr-icon vri-eye"></i>
            <span><?= isset($postItem->pageviews_count) ? numberFormatShort($postItem->pageviews_count) : numberFormatShort($postItem->pageviews); ?></span>
        </div>
    <?php endif; ?>
</div>
