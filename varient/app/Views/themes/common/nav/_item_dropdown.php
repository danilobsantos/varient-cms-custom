<?php if (!empty($navItem)): ?>
<li class="nav-item dropdown<?= uri_string() === $navItem->slug ? ' active' : ''; ?>">
<a class="nav-link" href="<?= generateNavItemUrl($navItem); ?>" data-bs-toggle="dropdown">
<?= esc($navItem->title); ?>
<svg class="mn-icon" viewBox="0 0 24 24" width="20" height="20">
<path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
</svg>
</a>
<ul class="dropdown-menu standard-dropdown">
<?php if (!empty($navItem->children)):
foreach ($navItem->children as $child):
if (!empty($child->children)):?>
<li class="dropdown-submenu">
<a class="dropdown-item" href="<?= generateNavItemUrl($child); ?>">
<?= esc($child->title); ?>
<svg class="mn-icon submenu-arrow" viewBox="0 0 24 24" width="20" height="20">
<path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
</svg>
</a>
<ul class="submenu vr-scrollbar">
<?php foreach ($child->children as $subChild): ?>
<li><a class="dropdown-item" href="<?= generateNavItemUrl($subChild); ?>"><?= esc($subChild->title); ?></a></li>
<?php endforeach; ?>
</ul>
</li>
<?php else: ?>
<li><a class="dropdown-item" href="<?= generateNavItemUrl($child); ?>"><?= esc($child->title); ?></a></li>
<?php endif;
endforeach;
endif; ?>
</ul>
</li>
<?php endif; ?>