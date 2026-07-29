<?php $imgAltText = !empty($postItem->alt_text) ? $postItem->alt_text : $postItem->title; ?>
<div class="post-item<?= checkPostImg($postItem, 'class'); ?> post-item-mid">
    <?php if (!empty($showLabel)): ?>
        <a href="<?= generateCategoryUrl($postItem); ?>">
            <span class="badge badge-category" style="background-color: <?= esc($postItem->cat_color); ?>"><?= esc($postItem->cat_name); ?></span>
        </a>
    <?php endif;
    if (checkPostImg($postItem)): ?>
        <div class="image ratio">
            <a href="<?= generatePostUrl($postItem); ?>"<?= postUrlNewTab($postItem); ?>>
                <img src="<?= getPostImageUrl($postItem, 'mid'); ?>" alt="<?= esc($imgAltText); ?>" class="img-fluid" width="306" height="182" loading="lazy"/>
                <?= getMediaIcon($postItem, 'media-md'); ?>
            </a>
        </div>
    <?php endif; ?>
    <h3 class="title"><a href="<?= generatePostUrl($postItem); ?>"<?= postUrlNewTab($this, $postItem); ?>><?= esc(characterLimiter($postItem->title, POST_DISPLAY_TITLE_LIMIT, '...')); ?></a></h3>
    <?= loadView('post/_post_meta', ['postItem' => $postItem]); ?>
</div>