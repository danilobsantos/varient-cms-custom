<?php if (!empty($hasSubCategories) || !empty($parentCategory)): ?>
    <div class="subcategories-wrapper mb-4 mt-2">

        <div class="swiper subcategory-swiper pb-2">
            <div class="swiper-wrapper">

                <?php if (!empty($parentCategory)): ?>
                    <div class="swiper-slide w-auto">
                        <a href="<?= generateCategoryUrl($parentCategory); ?>" class="subcategory-pill active d-inline-flex align-items-center text-nowrap">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-2 back-icon">
                                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
                            </svg>
                            <?= esc($parentCategory->name); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($subCategories)):
                    foreach ($subCategories as $subCategory): ?>
                        <div class="swiper-slide w-auto">
                            <a href="<?= generateCategoryUrl($subCategory); ?>" class="subcategory-pill d-inline-flex align-items-center text-nowrap">
                                <?= esc($subCategory->name); ?>
                            </a>
                        </div>
                    <?php endforeach;
                endif; ?>

            </div>
        </div>

    </div>
<?php endif; ?>