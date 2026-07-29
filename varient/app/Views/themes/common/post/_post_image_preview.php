<div class="post-image-preview mb-4">
    <?php if (!empty($postImages) && countItems($postImages) > 0): ?>
        <div class="d-block">
            <div class="swiper post-detail-slider" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <img src="<?= getPostImageUrl($post, 'default'); ?>" class="img-fluid" alt="<?= esc(!empty($post->alt_text) ? $post->alt_text : $post->title); ?>" width="856" height="570" loading="lazy"/>
                        <figcaption class="img-description"><?= esc($post->image_description); ?></figcaption>
                    </div>

                    <?php foreach ($postImages as $image):
                        $imgUrl = getStorageFileUrl($image->image_default, $image->storage); ?>
                        <div class="swiper-slide">
                            <img src="<?= esc($imgUrl); ?>" class="img-fluid" alt="<?= esc($post->title); ?>" width="856" height="570" loading="lazy"/>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="post-detail-slider-nav">
                    <button type="button" class="<?= $isRtl ? 'next-btn' : 'prev-btn'; ?>" aria-label="Previous Slide">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m15 18-6-6 6-6"/>
                        </svg>
                    </button>
                    <button type="button" class="fs-nav-btn <?= $isRtl ? 'prev-btn' : 'next-btn'; ?>" aria-label="Next Slide">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    <?php else:
        if (!empty($post->image_id) || !empty($post->image_url)):?>
            <img src="<?= getPostImageUrl($post, 'default'); ?>" class="img-fluid center-image" alt="<?= esc(!empty($post->alt_text) ? $post->alt_text : $post->title); ?>" width="856" height="570" loading="lazy"/>
            <?php if (!empty($post->image_description)): ?>
                <figcaption class="img-description"><?= esc($post->image_description); ?></figcaption>
            <?php endif; ?>
        <?php endif;
    endif; ?>
</div>