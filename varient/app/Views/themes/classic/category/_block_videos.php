<?php
$largePost = array_slice($categoryPosts, 0, 1);
$largePost = !empty($largePost) ? $largePost[0] : null;
$smallPosts = array_slice($categoryPosts, 1, 3);
?>

<div class="row">
    <div class="col-12 mb-4">
        <?php if (!empty($largePost)):
            $hasImage = !empty($largePost->image_id) || !empty($largePost->image_url) ? true : false; ?>

            <div class="post-featured ratio ratio-16x9">
                <a href="<?= generatePostUrl($largePost); ?>" aria-label="<?= esc($largePost->title); ?>"<?= postUrlNewTab($largePost); ?>>
                    <?php if ($hasImage):
                        $imgAltText = !empty($largePost->alt_text) ? $largePost->alt_text : $largePost->title; ?>
                        <img src="<?= getPostImageUrl($largePost, 'big'); ?>" alt="<?= esc($imgAltText); ?>" class="img-fluid img-post w-100 h-100 object-fit-cover" width="756" height="425" loading="lazy"/>
                        <?= getMediaIcon($largePost, 'media-lg'); ?>
                    <?php endif; ?>
                </a>
                <div class="caption">
                    <h3 class="title"><?= esc(characterLimiter($largePost->title, POST_DISPLAY_TITLE_LIMIT, '...')); ?></h3>
                    <?= loadView('post/_post_meta', ['postItem' => $largePost]); ?>
                </div>
            </div>

        <?php endif; ?>
    </div>
    <div class="col-12">
        <?php if (!empty($smallPosts)): ?>
            <div class="row">
                <?php foreach ($smallPosts as $item): ?>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <?= loadView('post/_post_item_mid', ['postItem' => $item, 'showLabel' => false]); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>