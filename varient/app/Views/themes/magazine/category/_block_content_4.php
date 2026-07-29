<?php
$limit = 8;
$colClass = $hasSidebar ? 'col-sm-12 col-md-6 col-lg-4' : 'col-sm-12 col-md-6 col-lg-3';
$displayPosts = !empty($categoryPosts) ? array_slice($categoryPosts, 0, $limit) : [];
?>

<?php if (!empty($displayPosts)): ?>
    <div class="row">
        <?php foreach ($displayPosts as $item): ?>
            <div class="<?= esc($colClass); ?>">
                <?= loadView("post/_post_item_mid", ['postItem' => $item, 'showLabel' => false]); ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>