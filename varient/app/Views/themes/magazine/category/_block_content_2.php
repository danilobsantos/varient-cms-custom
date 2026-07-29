<?php
$limitLarge = $hasSidebar ? 2 : 3;
$limitSmall = $hasSidebar ? 6 : 9;
$largePosts = array_slice($categoryPosts, 0, $limitLarge);
$smallPosts = array_slice($categoryPosts, $limitLarge, $limitSmall);
$gridItemClass = $hasSidebar ? 'col-lg-6' : 'col-lg-4';
?>

<?php if (!empty($largePosts)): ?>
    <div class="row">
        <?php foreach ($largePosts as $item): ?>
            <div class="col-sm-12 col-md-6 <?= $gridItemClass; ?>">
                <?= loadView("post/_post_item", ['postItem' => $item, 'showLabel' => false]); ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($smallPosts)): ?>
    <div class="row">
        <?php foreach ($smallPosts as $item): ?>
            <div class="col-sm-12 col-md-6 <?= $gridItemClass; ?>">
                <?= loadView('post/_post_item_small', ['postItem' => $item, 'showLabel' => false]); ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>