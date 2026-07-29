<?php
$largePosts = array_slice($categoryPosts, 0, 1);
$smallPosts = array_slice($categoryPosts, 1, 4);
?>

<div class="row">
    <div class="col-12 col-sm-6">
        <?php if (!empty($largePosts)): ?>
            <?php foreach ($largePosts as $item): ?>
                <?= loadView("post/_post_item", ['postItem' => $item, 'showLabel' => false]); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="col-12 col-sm-6">
        <?php if (!empty($smallPosts)): ?>
            <div class="row">
                <?php foreach ($smallPosts as $item): ?>
                    <div class="col-12">
                        <?= loadView('post/_post_item_small', ['postItem' => $item, 'showLabel' => false]); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>