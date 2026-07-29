<?= $this->extend($viewsPath . '/layout'); ?>

<?= $this->section('styles'); ?>
<link href="<?= base_url('assets/vendor/glightbox/css/glightbox.min.css'); ?>" rel="stylesheet">
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<section class="section section-page gallery-page page-gallery">
    <div class="container-xl">
        <?php if ($album): ?>
            <div class="album-header fade-in-up" style="animation-delay: 0.1s;">
                <div class="d-flex flex-start">
                    <a href="<?= generateURL('gallery'); ?>" class="btn-back">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="icon-svg">
                            <path d="m15 18-6-6 6-6"/>
                        </svg>
                        <?= trans("back_to_gallery"); ?>
                    </a>
                </div>
                <h1><?= esc($album->name); ?></h1>
                <div class="header-line"></div>
            </div>

            <?php if (!empty($categories)): ?>
                <div class="filter-container fade-in-up" style="animation-delay: 0.2s;">
                    <button class="btn btn-filter active" onclick="app.filterImages('All', this)"><?= trans("all"); ?></button>
                    <?php foreach ($categories as $cat): ?>
                        <button class="btn btn-filter" onclick="app.filterImages('<?= esc('cat-' . $cat->id); ?>', this);"><?= esc($cat->name); ?></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="masonry-grid is-loading" id="images-container">
                <div class="masonry-grid-sizer"></div>
                <?php if (!empty($images)): ?>
                    <?php foreach ($images as $image):
                        $imgUrl = getStorageFileUrl($image->path_small, $image->storage);
                        $imgUrlBig = getStorageFileUrl($image->path_big, $image->storage); ?>
                        <a href="<?= $imgUrlBig; ?>" class="masonry-grid-item glightbox" data-category="<?= esc('cat-' . $image->category_id); ?>" data-gallery="album-gallery"
                           data-title="<?= esc($image->title); ?>">
                            <img src="<?= $imgUrl; ?>" alt="<?= esc($image->title); ?>" class="photo-img" loading="lazy">
                            <div class="item-overlay">
                                <h4 class="item-title"><?= esc($image->title); ?></h4>
                            </div>
                        </a>
                    <?php endforeach;
                endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= base_url('assets/vendor/masonry/masonry.pkgd.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/masonry/imagesloaded.pkgd.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/glightbox/js/glightbox.min.js'); ?>"></script>
<script src="<?= base_url('assets/common/js/gallery.js'); ?>"></script>
<?= $this->endSection(); ?>
