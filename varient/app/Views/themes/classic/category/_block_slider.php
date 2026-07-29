<?php
$categoryPosts = $latestCategoryPosts[$category->id] ?? [];
$limitedPosts = array_slice($categoryPosts, 0, 20);
?>
<section class="section section-cat-slider">
    <div class="category-slider-wrapper" data-items="3" data-items-sm="2" data-items-md="2" data-items-lg="3" data-autoplay="5000" data-loop="true">
        <div class="section-title" style="border-bottom: 2px solid <?= esc($category->color); ?>">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="title" style="background-color: <?= esc($category->color); ?>">
                    <?= esc($category->name); ?>
                </h3>
                <div class="nav-sm-buttons">
                    <button class="btn-prev" aria-label="Previous">
                        <i class="vr-icon vri-angle-left"></i>
                    </button>
                    <button class="btn-next" aria-label="Next">
                        <i class="vr-icon vri-angle-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="section-content category-slider-container">
            <div class="swiper category-swiper" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
                <div class="swiper-wrapper">
                    <?php if (!empty($limitedPosts)):
                        foreach ($limitedPosts as $item): ?>
                            <div class="swiper-slide">
                                <?= loadView("post/_post_item_mid", ['postItem' => $item, 'showLabel' => true]); ?>
                            </div>
                        <?php endforeach;
                    endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>