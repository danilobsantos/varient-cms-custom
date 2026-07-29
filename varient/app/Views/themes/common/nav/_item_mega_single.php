<?php if (!empty($navItem)):
$navItemPosts = $latestCategoryPosts[$navItem->id] ?? [];
$numCols = 5; ?>
<li class="nav-item dropdown has-megamenu<?= uri_string() === $navItem->slug ? ' active' : ''; ?>">
<a class="nav-link" href="<?= generateNavItemUrl($navItem); ?>">
<?= esc($navItem->title); ?>
<svg class="mn-icon" viewBox="0 0 24 24" width="20" height="20">
<path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
</svg>
</a>
<div class="dropdown-menu megamenu">
<div class="megamenu-inner">
<div class="row">
<div class="col-12">
<?= loadCommonView('nav/_nav_posts', ['navItemPosts' => $navItemPosts, 'numCols' => $numCols]); ?>
</div>
</div>
</div>
</div>
</li>
<?php endif; ?>