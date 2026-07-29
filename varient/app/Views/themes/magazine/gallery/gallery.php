<?= $this->extend($viewsPath . '/layout'); ?>
<?= $this->section('content'); ?>
    <section class="section section-page gallery-page page-gallery">
        <div class="container-xl">

            <header class="gallery-header">
                <h1><?= trans("photo_gallery"); ?></h1>
                <p class="text-muted"><?= trans("photo_gallery_exp"); ?></p>
            </header>

            <div class="row g-4">
                <?php $counter = 0;
                if (!empty($albums)): ?>
                    <?php foreach ($albums as $album):
                        $cover = getStorageFileUrl($album->cover_path, $album->storage); ?>
                        <div class="col-lg-4 col-md-6 fade-in-up" style="animation-delay: 0">
                            <a href="<?= generateURL('gallery') . '/' . $album->id; ?>" class="gallery-item">
                                <img src="<?= $cover; ?>" alt="<?= esc($album->name) . '-' . $counter; ?>" class="gallery-img" loading="lazy">
                                <div class="view-text"><?= trans("discover"); ?></div>
                                <div class="gallery-overlay">
                                    <div class="content-wrap">
                                        <div class="d-flex align-items-center gap-2 album-meta">
                                            <span><?= formatDateClient($album->created_at); ?> </span>
                                            <span>/</span>
                                            <div class="d-flex align-items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="icon-svg">
                                                    <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/>
                                                    <circle cx="12" cy="13" r="3"/>
                                                </svg>
                                                <?= esc($album->image_count); ?>
                                            </div>
                                        </div>
                                        <h3 class="album-title"><?= esc(getDisplayTitle($album->name, 100)); ?></h3>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php $counter++;
                    endforeach;
                endif; ?>
            </div>

        </div>
    </section>
<?= $this->endSection(); ?>