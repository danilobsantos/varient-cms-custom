<?php
$vrPosts = array_slice($categoryPosts, 0, 4);
$smallPosts = array_slice($categoryPosts, 4, 9);
?>

<?php if (!empty($vrPosts)): ?>
    <div class="row">
        <?php foreach ($vrPosts as $item):
            $itemUrl = generatePostUrl($item);
            $imgUrl = getPostImageUrl($item, 'slider');
            $hasImage = postHasImage($item);
            $title = getDisplayTitle($item->title, 50);
            ?>
            <div class="col-sm-12 col-md-6 col-lg-3 col-post-item-vr">
                <div class="post-item post-item-vr">
                    <?php if ($hasImage): ?>
                        <div class="image">
                            <a href="<?= $itemUrl; ?>" class="img-link" aria-label="<?= esc($item->title); ?>"<?= postUrlNewTab($item); ?>>
                                <img src="<?= $imgUrl; ?>" alt="<?= esc(!empty($item->alt_text) ? $item->alt_text : $item->title); ?>" class="img-fluid" width="306" height="340" loading="lazy"/>
                                <?= getMediaIcon($item); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="caption">
                        <h3 class="title">
                            <a href="<?= $itemUrl; ?>" class="img-link text-white d-block"<?= postUrlNewTab($item); ?>>
                                <?= esc($title); ?>
                            </a>
                        </h3>
                        <?= loadView('post/_post_meta', ['postItem' => $item]); ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($smallPosts)): ?>
    <div class="row">
        <?php foreach ($smallPosts as $item): ?>
            <div class="col-sm-12 col-md-6 col-lg-4">
                <?= loadView('post/_post_item_small', ['postItem' => $item, 'showLabel' => false]); ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>