<?php
$vrPosts = array_slice($categoryPosts, 0, 2);
$smallPosts = array_slice($categoryPosts, 2, 6);
?>

<?php if (!empty($vrPosts)): ?>
    <div class="row">
        <?php foreach ($vrPosts as $item):
            $itemUrl = generatePostUrl($item);
            $imgUrl = getPostImageUrl($item, 'slider');
            $hasImage = postHasImage($item);
            $title = getDisplayTitle($item->title);
            ?>
            <div class="col-sm-12 col-md-6">
                <?= loadView('post/_post_item', ['postItem' => $item, 'showLabel' => false]); ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($smallPosts)): ?>
    <div class="row">
        <?php foreach ($smallPosts as $item): ?>
            <div class="col-sm-12 col-md-6">
                <?= loadView('post/_post_item_small', ['postItem' => $item, 'showLabel' => false]); ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>