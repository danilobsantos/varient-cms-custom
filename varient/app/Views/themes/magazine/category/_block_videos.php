<section class="section section-videos">
    <div class="container">
        <div class="section-header">
            <div>
                <h3 class="videos-title display-7"><?= esc($category->name); ?></h3>
            </div>
            <a href="<?= generateCategoryUrl($category); ?>" class="view-all">
                <?= trans("view_all"); ?>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>

        <div class="row g-4">
            <?php $catPosts = $latestCategoryPosts[$category->id] ?? [];
            if (!empty($catPosts)):
                $verticalPosts = array_slice($catPosts, 0, 2);
                foreach ($verticalPosts as $postItem): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="video-card card-vertical">
                            <a href="<?= generatePostUrl($postItem); ?>" class="main-post-link" aria-label="<?= esc($postItem->title, "attr"); ?>" <?= postUrlNewTab($postItem); ?>></a>
                            <img src="<?= getPostImageUrl($postItem, 'slider'); ?>" width="416" height="540" alt="<?= esc(!empty($postItem->alt_text) ? $postItem->alt_text : $postItem->title); ?>" loading="lazy">

                            <div class="media-icon media-lg">
                                <i class="vr-icon vri-play"></i>
                            </div>

                            <div class="card-content">
                                <span class="category-tag" style="color: <?= esc($postItem->cat_color); ?> !important"><?= esc($postItem->cat_name); ?></span>
                                <h3 class="video-title"><?= esc(getDisplayTitle($postItem->title, 60)); ?></h3>
                                <?= loadView('post/_post_meta', ['postItem' => $postItem]); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php
            $horizontalPosts = array_slice($catPosts, 2, 2);
            if (!empty($horizontalPosts)): ?>
                <div class="col-lg-4">
                    <div class="d-flex flex-column gap-4 h-100">
                        <?php foreach ($horizontalPosts as $postItem): ?>
                            <div class="video-card card-horizontal">
                                <a href="<?= generatePostUrl($postItem); ?>" class="main-post-link" aria-label="<?= esc($postItem->title, "attr"); ?>" <?= postUrlNewTab($postItem); ?>></a>
                                <img src="<?= getPostImageUrl($postItem, 'mid'); ?>" width="416" height="258" alt="<?= esc(!empty($postItem->alt_text) ? $postItem->alt_text : $postItem->title); ?>" loading="lazy">

                                <div class="media-icon media-md">
                                    <i class="vr-icon vri-play"></i>
                                </div>

                                <div class="card-content">
                                    <span class="category-tag" style="color: <?= esc($postItem->cat_color); ?> !important"><?= esc($postItem->cat_name); ?></span>
                                    <h3 class="video-title"><?= esc(characterLimiter($postItem->title, 55, '...')); ?></h3>
                                    <?= loadView('post/_post_meta', ['postItem' => $postItem]); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>