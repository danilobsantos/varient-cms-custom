<?php
$postUrl = generatePostUrl($postItem);
$categoryUrl = generateCategoryUrl($postItem);
$imgUrl = getPostImageUrl($postItem, 'mid');
$imgAltText = !empty($postItem->alt_text) ? $postItem->alt_text : $postItem->title;
$hasImage = !empty($postItem->image_id) || !empty($postItem->image_url) ? true : false;
$extraClass = !$hasImage ? ' post-item-no-image' : '';
$shouldShowCat = !empty($showLabel) && !empty($postItem->cat_name);
?>
<div class="post-item-horizontal<?= esc($extraClass); ?>">
    <div class="row">
        <?php if ($shouldShowCat): ?>
            <a href="<?= $categoryUrl; ?>" class="category-link" title="<?= esc($postItem->cat_name); ?>">
                <span class="badge badge-category" style="background-color: <?= esc($postItem->cat_color); ?>">
                    <?= esc($postItem->cat_name); ?>
                </span>
            </a>
        <?php endif; ?>

        <?php if ($hasImage): ?>
            <div class="col-12 col-sm-5">
                <div class="image ratio">
                    <a href="<?= $postUrl; ?>" aria-label="<?= esc($postItem->title); ?>"<?= postUrlNewTab($postItem); ?>>
                        <img src="<?= $imgUrl; ?>" alt="<?= esc($imgAltText); ?>" class="img-fluid h-100 object-fit-cover" width="350" height="225" loading="lazy"/>
                        <?= getMediaIcon($postItem); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-12 col-sm-7">
            <h3 class="title">
                <a href="<?= $postUrl; ?>"<?= postUrlNewTab($postItem); ?>>
                    <?= esc(characterLimiter($postItem->title, POST_DISPLAY_TITLE_LIMIT, '...')); ?>
                </a>
            </h3>
            <?= loadView('post/_post_meta', ['postItem' => $postItem]); ?>
            <p class="description">
                <?= esc(characterLimiter($postItem->summary, POST_DISPLAY_SUMMARY_LIMIT, '...')); ?>
            </p>
        </div>
    </div>
</div>