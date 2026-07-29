<?php $numCols = (isset($numCols) && $numCols == 5) ? 5 : 4; ?>
<div class="row <?= $numCols === 5 ? 'row-cols-5' : 'row-cols-4'; ?>">
<?php if (!empty($navItemPosts)):
foreach (array_slice($navItemPosts, 0, $numCols) as $postNav): ?>
<div class="col animate-fade">
<a href="<?= generatePostUrl($postNav); ?>" class="menu-post-card">
<div class="menu-post-img-wrapper">
<div class="ratio">
<img src="<?= getPostImageUrl($postNav, 'mid'); ?>" width="450" height="280" alt="<?= esc(!empty($postNav->alt_text) ? $postNav->alt_text : $postNav->title); ?>" class="img-fluid menu-post-img" loading="lazy"/>
</div>
<?= getMediaIcon($postNav, 'media-md'); ?>
</div>
<h3 class="menu-post-title"><?= esc(getDisplayTitle($postNav->title, 36)); ?></h3>
</a>
<div class="post-meta">
<?php if ((int)$config->show_post_author === 1): ?>
<div class="meta-item">
<a href="<?= generateProfileURL($postNav->author_slug); ?>" class="a-username"><?= esc(characterLimiter($postNav->author_username, 18, '...')); ?></a>
</div>
<?php endif;
if ((int)$config->show_post_date === 1): ?>
<div class="meta-item">
<span><?= formatDateClient($postNav->created_at); ?></span>
</div>
<?php endif; ?>
</div>
</div>
<?php endforeach;
endif; ?>
</div>