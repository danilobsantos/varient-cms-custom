<?php
$uri = uri_string();
?>
<div id="mega-menu-wrapper" class="mega-menu-wrapper">
<div class="container-xl">
<nav class="navbar navbar-expand-lg">
<div class="container-fluid px-0 position-relative">

<?php if ($activeTheme->theme !== 'classic'):
$logoSize = getLogoSize(); ?>
<a href="<?= langBaseUrl(); ?>" class="navbar-brand" aria-label="<?= trans("homepage", "attr"); ?>">
<?php if ($activeTheme->theme === 'news'): ?>
<img src="<?= getLogo('dark'); ?>" class="logo" alt="<?= trans("app_name", "attr"); ?> Logo" width="<?= $logoSize->width; ?>" height="<?= $logoSize->height; ?>">
<?php else: ?>
<img src="<?= getLogo('light'); ?>" class="logo js-logo-light" alt="<?= trans("app_name", "attr"); ?> Logo" width="<?= $logoSize->width; ?>" height="<?= $logoSize->height; ?>">
<img src="<?= getLogo('dark'); ?>" class="logo js-logo-dark" alt="<?= trans("app_name", "attr"); ?> Logo" width="<?= $logoSize->width; ?>" height="<?= $logoSize->height; ?>">
<?php endif; ?>
</a>
<?php endif; ?>

<div class="collapse navbar-collapse">
<ul class="navbar-nav ms-4 mb-2 mb-lg-0 align-items-stretch">
<?php if ($config->show_home_link): ?>
<li class="nav-item<?= $uri === 'index' || $uri === '' || $uri === '/' ? ' active' : ''; ?>">
<a class="nav-link" href="<?= langBaseUrl(); ?>"><?= trans("home"); ?></a>
</li>
<?php endif; ?>

<?php
if (!empty($menuLinks)):
foreach ($menuLinks as $item):
if ($item->location === 'main' && $item->parent_id == 0):
$viewData = [
'navItem'             => $item,
'latestCategoryPosts' => $latestCategoryPosts ?? []
];

if (!empty($item->children)) {
if ($item->type === 'category') {
echo loadCommonView('nav/_item_mega', $viewData);
} else {
echo loadCommonView('nav/_item_dropdown', $viewData);
}
} else {
if ($item->type === 'category') {
echo loadCommonView('nav/_item_mega_single', $viewData);
} else { ?>
<li class="nav-item<?= $uri === $item->slug ? ' active' : ''; ?>">
<a class="nav-link" href="<?= generateNavItemUrl($item); ?>">
<?= esc($item->title); ?>
</a>
</li>
<?php }
}
endif;
endforeach;
endif; ?>
</ul>

<div class="d-flex align-items-center ms-auto gap-2">
<button id="header-search-btn" class="search-icon-btn" aria-label="Search">
<svg xmlns="http://www.w3.org/2000/svg" class="icon-search" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<circle cx="11" cy="11" r="8"></circle>
<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
</svg>
<svg class="mn-icon icon-close d-none" viewBox="0 0 24 24" width="21" height="21">
<path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
</svg>
</button>

<?php if ($premiumMembership->subscriptionStatus && $premiumMembership->subscriptionButtonVisibility && !hasPremiumAccess()): ?>
<a href="<?= generateURL("subscription", "plans"); ?>" class="btn-nav-subscribe" title="<?= trans("btn_subscribe"); ?>" aria-label="<?= trans("btn_subscribe"); ?>" rel="bookmark">
<?= trans("btn_subscribe"); ?>
</a>
<?php endif; ?>
</div>
</div>

<div id="header-search-box" class="header-search-dropdown">
<form action="<?= generateURL('search'); ?>" method="get" class="form-header-search d-flex gap-2">
<input type="text" name="q" class="form-control search-form-control" placeholder="<?= trans("search", "attr"); ?>..." aria-label="<?= trans("search", "attr"); ?>" maxlength="300">
<button type="submit" class="btn btn-search" aria-label="<?= trans("search", "attr"); ?>" title="<?= trans("search", "attr"); ?>">
<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
<circle cx="11" cy="11" r="8"></circle>
<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
</svg>
</button>
</form>
</div>
</div>
</nav>
</div>
</div>

<script>!function(){const e=document.querySelector(".navbar-nav"),t=document.querySelector(".navbar-collapse"),n=document.querySelector(".navbar-collapse > .ms-auto"),o=document.querySelector(".navbar");if(!(e&&t&&n&&o))return;let r=[],a=null,s=null;const l=()=>{if(a||(r=Array.from(e.children).filter(e=>!e.classList.contains("js-dynamic-more")),(a=document.createElement("li")).className="nav-item dropdown d-none js-dynamic-more",a.innerHTML='<a class="nav-link nav-shrink-transition" href="#" data-bs-toggle="dropdown" aria-expanded="false">'+VrConfig.text.more+' <svg class="mn-icon" viewBox="0 0 24 24" width="20" height="20"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/></svg></a><ul class="dropdown-menu standard-dropdown shadow-sm border-0 mt-2"></ul>',e.appendChild(a),s=a.querySelector(".dropdown-menu")),s.innerHTML="",r.forEach(e=>e.classList.remove("d-none")),a.classList.add("d-none"),e.style.flexWrap="nowrap",t.style.flexWrap="nowrap",window.innerWidth<992)return e.style.flexWrap="",void e.classList.add("is-calculated");const o=e.style.display;e.style.display="none";const l=n.getBoundingClientRect();e.style.display=o;const d=e.getBoundingClientRect(),i="undefined"!=typeof VrConfig&&VrConfig.isRtl?d.right-l.right-5:l.left-d.left-5;let c=0,m=-1;for(let e=0;e<r.length;e++)if((c+=r[e].offsetWidth)>i){m=e;break}if(-1!==m){a.classList.remove("d-none");const e=a.offsetWidth;c=0,m=-1;for(let t=0;t<r.length;t++)if((c+=r[t].offsetWidth)>i-e){m=t;break}if(-1!==m)for(let e=m;e<r.length;e++){const t=r[e];t.classList.add("d-none");const n=t.querySelector("a.nav-link");if(n){const e=n.cloneNode(!0);e.querySelectorAll("svg").forEach(e=>e.remove());const o=e.textContent.trim(),r=document.createElement("li");let a=[];if(t.classList.contains("has-megamenu")?a=Array.from(t.querySelectorAll(".subcat-list .subcat-link")):t.querySelector(".standard-dropdown")&&(a=Array.from(t.querySelectorAll(".standard-dropdown > li > a.dropdown-item"))),a.length>0){r.className="dropdown-submenu";const e=document.createElement("a");e.className="dropdown-item",e.href=n.href,e.innerHTML=o+' <svg class="mn-icon submenu-arrow" viewBox="0 0 24 24" width="20" height="20"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"></path></svg>',r.appendChild(e);const t=document.createElement("ul");t.className="submenu vr-scrollbar",a.forEach(e=>{const n=document.createElement("li"),o=document.createElement("a");o.className="dropdown-item",o.href=e.href;const r=e.cloneNode(!0);r.querySelectorAll("svg").forEach(e=>e.remove()),o.textContent=r.textContent.trim(),n.appendChild(o),t.appendChild(n)}),r.appendChild(t)}else{const e=document.createElement("a");e.className="dropdown-item",e.href=n.href,e.textContent=o,r.appendChild(e)}s.appendChild(r)}}}e.classList.add("is-calculated")};let d;const i=()=>{clearTimeout(d),d=setTimeout(l,20)};document.fonts&&document.fonts.ready&&document.fonts.ready.then(i),window.addEventListener("load",i),window.ResizeObserver?new ResizeObserver(()=>{requestAnimationFrame(i)}).observe(o):window.addEventListener("resize",i),l()}();</script>