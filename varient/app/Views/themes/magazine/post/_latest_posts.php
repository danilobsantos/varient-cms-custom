<?php if ($config->show_latest_posts == 1):
    if (!empty($latestPosts) && countItems($latestPosts) > 0):
        $widgets = getCategoryWidgets(0, $widgets, $adSpaces, $activeLang->id); ?>
        <section class="section">
            <div class="container-xl">
                <div class="row">
                    <div class="col-sm-12 col-md-12<?= $widgets->hasWidgets ? ' col-lg-8' : ''; ?>">
                        <div class="latest-posts">
                            <div class="section-title">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h3 class="title"><?= trans("latest_posts"); ?></h3>
                                    <a href="<?= generateURL('posts'); ?>" class="view-all font-title"><?= trans("view_all_posts"); ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                            <polyline points="12 5 19 12 12 19"></polyline>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            <div class="section-content">
                                <div id="postsLoadMoreContent" class="row">
                                    <?php $i = 0;
                                    if (!empty($latestPosts)):
                                        foreach ($latestPosts as $item):
                                            if ($i < $postsPerPage): ?>
                                                <div class="col-sm-12<?= $widgets->hasWidgets ? ' col-md-6' : ' col-md-4'; ?>">
                                                    <?= loadView("post/_post_item", ['postItem' => $item, 'showLabel' => true]); ?>
                                                </div>
                                            <?php endif;
                                            $i++;
                                        endforeach;
                                    endif; ?>
                                </div>
                                <?php if (countItems($latestPosts) > $postsPerPage): ?>
                                    <div class="search-load-more-container">
                                        <div class="d-flex justify-content-center mt-5 mb-5 px-3">
                                            <button class="btn btn-custom btn-load-more-posts" data-lang-id="<?= $activeLang->id; ?>" data-type="latest" data-view-type="vertical">
                                            <span class="btn-text">
                                                <?= trans("load_more", true); ?>
                                            </span>
                                                <svg class="btn-icon-svg" width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z" fill="currentColor"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($widgets->hasWidgets):
                        echo loadView('partials/_sidebar_category', ['objectWidgets' => $widgets]);
                    endif; ?>
                </div>
            </div>
        </section>
    <?php endif;
endif; ?>