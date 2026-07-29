<?php
$postUrl = generatePostUrl($postItem);
$imgUrl = getPostImageUrl($postItem, 'small');
$imgAltText = !empty($postItem->alt_text) ? $postItem->alt_text : $postItem->title;
$hasImage = !empty($postItem->image_id) || !empty($postItem->image_url);
$extraClass = !$hasImage ? ' post-item-no-image' : '';
?>

<div class="d-flex align-items-start post-item-small mb-4">
    <div class="image">
        <a href="<?= $postUrl; ?>" class="flex-shrink-0" aria-label="<?= esc($postItem->title); ?>"<?= postUrlNewTab($postItem); ?>>
            <img src="<?= $imgUrl; ?>" alt="<?= esc($imgAltText); ?>" class="img-fluid" width="110" height="78" loading="lazy"/>
            <?= getMediaIcon($postItem, 'media-sm'); ?>
        </a>
    </div>

    <div class="flex-grow-1 ms-3">
        <h3 class="title">
            <a href="<?= $postUrl; ?>"<?= postUrlNewTab($postItem); ?> class="d-block"><?= getDisplayTitle($postItem->title, 45); ?></a>
        </h3>
        <?= loadView('post/_post_meta', ['postItem' => $postItem]); ?>
    </div>
</div>