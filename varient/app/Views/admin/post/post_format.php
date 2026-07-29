<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$postFormats = getAppDefault('postFormats');

$activeFormats = array_filter($postFormats, function ($key) use ($config) {
    return isPostFormatActive($key, $config);
}, ARRAY_FILTER_USE_KEY);

?>

    <div class="container-xxl container-post-format">
        <div class="text-center mb-12">
            <h1 class="fw-bold fs-1 text-gray-900"><?= trans("choose_post_format"); ?></h1>
            <p class="text-gray-700"><?= trans("choose_post_format_exp"); ?></p>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 g-lg-5">
            <?php foreach ($activeFormats as $key => $format): ?>
                <div class="col">
                    <a href="<?= adminUrl('add-post?format=' . $key); ?>" class="post-format-card">
                        <div class="card-icon-container">
                            <?= $format['icon']; ?>
                        </div>
                        <h4 class="card-title"><?= trans($format['title']); ?></h4>
                        <p class="card-text"><?= trans($format['desc']); ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<?= $this->endSection(); ?>