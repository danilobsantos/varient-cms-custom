<?= $this->extend($viewsPath . '/layout'); ?>
<?= $this->section('content'); ?>

    <section id="postDetailsPage" class="section section-page" data-id="<?= $post->id; ?>">
        <div class="container-xl">
            <div class="row<?= $isFullWidth ? ' justify-content-center' : ''; ?>">

                <?php if (!$isFullWidth): ?>
                    <?= loadCommonView('post/_breadcrumb_post'); ?>
                <?php endif; ?>

                <div class="col-md-12 <?= $isFullWidth ? 'col-lg-9' : 'col-lg-8'; ?>">
                    <div class="post-content">
                        <div class="d-flex justify-content-center align-items-center mb-3">
                            <div class="bd-highlight">
                                <a href="<?= generateCategoryUrl($post); ?>">
                                    <span class="badge badge-category" style="background-color: <?= esc($post->cat_color); ?>"><?= esc($post->cat_name); ?></span>
                                </a>

                                <?php if ((int)$post->status !== 1 || (int)$post->visibility !== 1 || (int)$post->is_scheduled === 1): ?>
                                    <span class="badge badge-category bg-danger"><?= trans("preview"); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="bd-highlight ms-auto">
                                <?php if (authCheck() && (hasPermission('manage_all_posts') || user()->id == $post->user_id)): ?>
                                    <a href="<?= adminUrl('posts/edit/' . $post->id); ?>" class="btn btn-xs btn-warning gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                            <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
                                        </svg><?= trans("edit"); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <h1 class="post-title"><?= esc($post->title); ?></h1>

                        <?php
                        echo loadCommonView('premium/_paywall', [
                                'restrictionType' => $restrictionType,
                                'paywallType'     => 'strict'
                        ]);
                        ?>

                    </div>
                </div>

                <?php if (!$isFullWidth): ?>
                    <div class="col-md-12 col-lg-4">
                        <?= loadView('partials/_sidebar'); ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section>

    <div id="post-tracker-data" data-post-id="<?= $post->id; ?>" data-time-spent="<?= esc($postTimeSpent); ?>" class="d-none"></div>

<?= $this->endSection(); ?>