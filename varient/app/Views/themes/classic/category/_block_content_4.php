<?php
$limit = 6;
$displayPosts = !empty($categoryPosts) ? array_slice($categoryPosts, 0, $limit) : [];
?>

<?php if (!empty($displayPosts)): ?>
    <div class="row">
        <?php foreach ($displayPosts as $item): ?>
            <div class="col-sm-12 col-md-6 col-lg-4">
                <?= loadView("post/_post_item_mid", ['postItem' => $item, 'showLabel' => false]); ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>