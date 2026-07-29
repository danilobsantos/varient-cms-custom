<?php
$postFormats = getAppDefault('postFormats');
$activeFormats = array_filter($postFormats, function ($key) use ($config) {
    return isPostFormatActive($key, $config);
}, ARRAY_FILTER_USE_KEY);
?>

<div class="custom-modal-wrapper">
    <div class="modal fade post-format-modal" id="addPostModal" tabindex="-1" aria-labelledby="addPostModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <h4 class="modal-title" id="addPostModalLabel">
                        <?= trans("choose_post_format"); ?>
                    </h4>
                    <p class="modal-subtitle">
                        <?= trans("choose_post_format_exp"); ?>
                    </p>
                </div>

                <div class="modal-body">
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                        <?php foreach ($activeFormats as $key => $format): ?>
                            <div class="col">
                                <a href="<?= adminUrl('add-post?format=' . $key); ?>" class="format-card">
                                    <div class="icon-wrapper">
                                        <?= $format['icon']; ?>
                                    </div>
                                    <h4 class="card-title">
                                        <?= trans($format['title']); ?>
                                    </h4>
                                    <p class="card-desc">
                                        <?= trans($format['desc']); ?>
                                    </p>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>