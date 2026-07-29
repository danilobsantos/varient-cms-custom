<?php
$firstPost = null;
if ($highlightFirst):
    $firstPost = $recommendedPosts[0] ?? null;
    if (!empty($firstPost)): ?>
        <?= loadView('post/_post_item', ['postItem' => $firstPost, 'showLabel' => true]) ?>
    <?php endif;
endif;


if (!empty($recommendedPosts)):
    foreach ($recommendedPosts as $item):
        if ($highlightFirst && !empty($firstPost) && $firstPost->id == $item->id) {
            continue;
        } ?>
        <?= loadView('post/_post_item_small', ['postItem' => $item, 'showLabel' => false]) ?>
    <?php endforeach;
endif; ?>