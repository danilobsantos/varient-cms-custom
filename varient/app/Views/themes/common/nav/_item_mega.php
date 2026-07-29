<?php if (!empty($navItem)):
$navItemPosts = $latestCategoryPosts[$navItem->id] ?? [];
$numCols = 4; ?>
<li class="nav-item dropdown has-megamenu<?= uri_string() === $navItem->slug ? ' active' : ''; ?>">
<a class="nav-link" href="<?= generateNavItemUrl($navItem); ?>">
<?= esc($navItem->title); ?>
<svg class="mn-icon" viewBox="0 0 24 24" width="20" height="20">
<path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
</svg>
</a>
<div id="mn<?= $navItem->id; ?>" class="dropdown-menu megamenu">
<div class="container-fluid px-0">
<div class="row g-0">
<div class="col menu-sidebar-col vr-scrollbar">
<ul class="subcat-list">
<li class="subcat-item">
<a href="<?= generateNavItemUrl($navItem); ?>" class="subcat-link active" data-id="<?= $navItem->id; ?>"><?= trans("all"); ?></a>
</li>
<?php if (!empty($navItem->children)):
foreach ($navItem->children as $child): ?>
<li class="subcat-item">
<a href="<?= generateNavItemUrl($child); ?>" class="subcat-link" data-id="<?= $child->id; ?>"><?= esc($child->title); ?></a>
</li>
<?php endforeach;
endif; ?>
</ul>
</div>
<div class="col mega-posts-col">
<div class="mega-posts-container">
<div class="minimal-loader">
<div class="spinner-ring"></div>
</div>
<div class="js-posts-grid">
<?= loadCommonView('nav/_nav_posts', ['navItemPosts' => $navItemPosts, 'numCols' => $numCols]); ?>
</div>
</div>
</div>
</div>
</div>
</div>
</li>
<?php endif; ?>