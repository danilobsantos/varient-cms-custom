<?= $this->extend($viewsPath . '/layout'); ?>
<?= $this->section('content'); ?>

    <section class="section section-page">
        <div class="container-xl">
            <div class="row">
                <div class="col-12">
                    <?= loadCommonView('post/_posts_breadcrumb'); ?>
                </div>

                <div class="col-12">
                    <h1 class="page-title"><?= !empty($pageTitle) ? esc($pageTitle) : esc($title); ?></h1>
                    <div class="row">

                        <div class="col-12">
                            <?= loadCommonView('partials/_category_subcategories'); ?>
                        </div>

                        <div class="col-sm-12 col-md-12 col-lg-8">
                            <div id="postsLoadMoreContent" class="row">
                                <?php $i = 0;
                                if (!empty($posts)):
                                    foreach ($posts as $item):
                                        if ($i == 2):
                                            echo loadCommonView('partials/_ad_spaces', ['adSpace' => 'posts_top', 'class' => 'mb-4']);
                                        endif; ?>
                                        <div class="col-sm-12 col-md-6">
                                            <?= loadView('post/_post_item', ['postItem' => $item, 'showLabel' => false]); ?>
                                        </div>
                                        <?php $i++;
                                    endforeach;
                                else:?>
                                    <p class="text-center text-muted">
                                        <?= trans("no_results_found"); ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <?= loadCommonView('partials/_ad_spaces', ['adSpace' => 'posts_bottom', 'class' => '']); ?>
                                </div>

                                <?php if ($pageType === 'search'):
                                    if ($hasMore): ?>
                                        <div class="col-12 search-load-more-container">
                                            <div class="d-flex justify-content-center mt-5 mb-5 px-3">
                                                <button class="btn btn-custom btn-load-more-posts" data-lang-id="<?= $activeLang->id; ?>" data-type="search" data-view-type="vertical">
                                                    <span class="btn-text">
                                                        <?= trans("load_more", true); ?>
                                                    </span>
                                                    <svg class="btn-icon-svg" width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z" fill="currentColor"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endif;
                                else: ?>
                                    <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
                                        <div class="col-12 mt-5">
                                            <?= $pager->links(); ?>
                                        </div>
                                    <?php endif;
                                endif; ?>
                            </div>
                        </div>

                        <div class="col-sm-12 col-md-12 col-lg-4">
                            <?= loadView('partials/_sidebar'); ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

<?= $this->endSection(); ?>