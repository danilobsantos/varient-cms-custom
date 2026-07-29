<?php if (!empty($popularPosts)):
    foreach ($popularPosts as $item): ?>
        <?= loadView('post/_post_item_small', ['postItem' => $item, 'showLabel' => false]); ?>
    <?php endforeach;
endif; ?>