<?= $this->extend($viewsPath . '/layout'); ?>
<?= $this->section('content'); ?>

    <section class="section section-page">
        <div class="container-xl">
            <div class="row">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= langBaseUrl(); ?>"><?= trans("home"); ?></a></li>
                        <li class="breadcrumb-item active"><?= trans("rss_feeds"); ?></li>
                    </ol>
                </nav>

                <h1 class="page-title"><?= trans("rss_feeds"); ?></h1>

                <div class="page-content rss-feeds">

                    <div class="d-flex justify-content-end">
                        <h2 class="text-muted small d-flex align-items-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                                <path d="m9 9 5 12 1.8-5.2L21 14Z"/>
                                <path d="M7.2 2.2 8 5.1"/>
                                <path d="m5.1 8-2.9-.8"/>
                                <path d="M14 4.1 12 6"/>
                                <path d="m6 12-1.9 2"/>
                            </svg>
                            <?= trans("click_copy_icon_copy_url"); ?>
                        </h2>
                    </div>

                    <div class="row">
                        <div class="col-md-12 col-lg-6 mb-4">
                            <div class="rss-card featured">
                                <div class="card-header-flex">
                                    <div class="icon-box icon-box-latest-posts">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.1.243-2.143.7-3.1 1.1 3.1 3 5.3 4.3 6.6z"/>
                                        </svg>
                                    </div>
                                    <h3 class="card-title"><?= trans("latest_posts"); ?></h3>
                                </div>
                                <div class="url-input-group">
                                    <input type="text" class="url-input" value="<?= generateURL('rss_feeds'); ?>/feed/latest" readonly id="rss-latest-card">
                                    <button type="button" class="copy-btn js-rss-copy-btn" data-target="#rss-latest-card" aria-label="Copy to clipboard">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>
                                            <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($categoryTree)):
                            foreach ($categoryTree as $category):
                                $copyId = 'rss-cat-' . $category->id; ?>
                                <div class="col-md-12 col-lg-6 mb-4">
                                    <div class="rss-card">
                                        <div class="card-header-flex">
                                            <div class="icon-box">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M4 11a9 9 0 0 1 9 9"/>
                                                    <path d="M4 4a16 16 0 0 1 16 16"/>
                                                    <circle cx="5" cy="19" r="1"/>
                                                </svg>
                                            </div>
                                            <h3 class="card-title"><?= esc($category->name); ?></h3>
                                        </div>
                                        <div class="url-input-group">
                                            <input type="text" class="url-input" value="<?= generateURL('rss_feeds') . '/feed/category/' . esc($category->slug); ?>" readonly id="<?= $copyId; ?>">
                                            <button type="button" class="copy-btn js-rss-copy-btn" data-target="#<?= $copyId; ?>" aria-label="Copy to clipboard">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>
                                                    <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach;
                        endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?= $this->endSection(); ?>