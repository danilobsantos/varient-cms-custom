<section class="section featured-section">
    <div class="container-xl">
        <div class="fs-grid-wrapper">
            <div class="fs-grid-item area-main">
                <div class="main-slider-wrapper">
                    <?php if (countItems($sliderPosts) > 0): ?>
                        <div class="swiper main-slider position-relative" <?= $isRtl ? 'dir="rtl"' : 'dir="ltr"'; ?>>
                            <div class="swiper-wrapper">
                                <?php $count = 0; ?>
                                <?php foreach ($sliderPosts as $item): ?>
                                    <div class="swiper-slide">
                                        <div class="fs-card">
                                            <a href="<?= generateCategoryUrl($item); ?>" class="fs-badge" title="<?= esc($item->cat_name); ?>">
                                            <span class="badge badge-category" style="background-color: <?= esc($item->cat_color); ?>">
                                                <?= esc($item->cat_name); ?>
                                            </span>
                                            </a>
                                            <a href="<?= generatePostUrl($item); ?>" class="fs-main-link" <?= postUrlNewTab($item); ?> aria-label="<?= esc($item->title); ?>"></a>
                                            <img src="<?= getPostImageUrl($item, 'slider'); ?>" alt="<?= esc(!empty($item->alt_text) ? $item->alt_text : $item->title); ?>" class="fs-img" <?= $count === 0 ? 'fetchpriority="high"' : 'loading="lazy"'; ?>>
                                            <?= getMediaIcon($item, 'media-lg'); ?>
                                            <div class="fs-content">
                                                <?php if ($count < 1): ?>
                                                    <h2 class="fs-title">
                                                        <a href="<?= generatePostUrl($item); ?>"><?= esc(characterLimiter($item->title, 120, '...')); ?></a>
                                                    </h2>
                                                <?php else: ?>
                                                    <h3 class="fs-title">
                                                        <a href="<?= generatePostUrl($item); ?>"><?= esc(characterLimiter($item->title, 120, '...')); ?></a>
                                                    </h3>
                                                <?php endif; ?>
                                                <div class="fs-meta">
                                                    <?= loadView('post/_post_meta', ['postItem' => $item]); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php $count++; ?>
                                <?php endforeach; ?>
                            </div>
                            <div class="swiper-pagination"></div>
                            <div class="position-absolute top-0 bottom-0 start-0 d-flex align-items-center ps-2 z-3 pointer-events-none">
                                <button type="button" class="fs-nav-btn <?= $isRtl ? 'next-btn' : 'prev-btn'; ?> pointer-events-auto" aria-label="Previous Slide">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 404.258 404.258">
                                        <polygon points="289.927,18 265.927,0 114.331,202.129 265.927,404.258 289.927,386.258 151.831,202.129 "/>
                                    </svg>
                                </button>
                            </div>
                            <div class="position-absolute top-0 bottom-0 end-0 d-flex align-items-center pe-2 z-3 pointer-events-none">
                                <button type="button" class="fs-nav-btn <?= $isRtl ? 'prev-btn' : 'next-btn'; ?> pointer-events-auto" aria-label="Next Slide">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 404.258 404.258">
                                        <polygon points="138.331,0 114.331,18 252.427,202.129 114.331,386.258 138.331,404.258 289.927,202.129 "/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
            $featTop = !empty($featuredPosts) ? array_slice($featuredPosts, 0, 2) : [];
            $featBottom = !empty($featuredPosts) ? array_slice($featuredPosts, 2, 2) : [];
            ?>
            <div class="fs-grid-item fs-grid-boxes area-sub-top">
                <div class="sub-grid">
                    <?php foreach ($featTop as $item): ?>
                        <div class="fs-card">
                            <a href="<?= generateCategoryUrl($item); ?>" class="fs-badge" title="<?= esc($item->cat_name); ?>">
                                <span class="badge badge-category" style="background-color: <?= esc($item->cat_color); ?>">
                                    <?= esc($item->cat_name); ?>
                                </span>
                            </a>
                            <a href="<?= generatePostUrl($item); ?>" class="fs-main-link" <?= postUrlNewTab($item); ?> aria-label="<?= esc($item->title); ?>"></a>
                            <img src="<?= getPostImageUrl($item, 'slider'); ?>" alt="<?= esc(!empty($item->alt_text) ? $item->alt_text : $item->title); ?>" class="fs-img" loading="lazy">
                            <?= getMediaIcon($item); ?>
                            <div class="fs-content">
                                <h3 class="fs-title">
                                    <a href="<?= generatePostUrl($item); ?>"><?= esc(getDisplayTitle($item->title, 50)); ?></a>
                                </h3>
                                <div class="fs-meta">
                                    <?= loadView("post/_post_meta", ['postItem' => $item]); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="fs-grid-item fs-grid-boxes area-sub-bottom">
                <div class="sub-grid">
                    <?php foreach ($featBottom as $item): ?>
                        <div class="fs-card">
                            <a href="<?= generateCategoryUrl($item); ?>" class="fs-badge" title="<?= esc($item->cat_name); ?>">
                                <span class="badge badge-category" style="background-color: <?= esc($item->cat_color); ?>">
                                    <?= esc($item->cat_name); ?>
                                </span>
                            </a>
                            <a href="<?= generatePostUrl($item); ?>" class="fs-main-link" <?= postUrlNewTab($item); ?> aria-label="<?= esc($item->title); ?>"></a>
                            <img src="<?= getPostImageUrl($item, 'slider'); ?>" alt="<?= esc(!empty($item->alt_text) ? $item->alt_text : $item->title); ?>" class="fs-img" loading="lazy">
                            <?= getMediaIcon($item); ?>
                            <div class="fs-content">
                                <h3 class="fs-title">
                                    <a href="<?= generatePostUrl($item); ?>"><?= esc(getDisplayTitle($item->title, 50)); ?></a>
                                </h3>
                                <div class="fs-meta">
                                    <?= loadView("post/_post_meta", ['postItem' => $item]); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>