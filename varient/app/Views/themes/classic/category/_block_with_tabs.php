<?php
$hasSidebar = !empty($categoryWidgets->hasWidgets) ? true : false;
$blockClass = !empty($category->block_type) ? 'section-cat-' . esc($category->block_type) : '';
$blockMap = [
        'block-1' => '_block_content_1',
        'block-2' => '_block_content_2',
        'block-3' => '_block_content_3',
        'block-4' => '_block_content_4',
        'block-6' => '_block_videos',
];
$contentContainerId = 'tabCategoryContent_' . esc($category->id);
?>

<div class="section section-category">
    <div class="row">
        <div class="col-sm-12 col-md-12">

            <div class="section-title" style="border-bottom: 2px solid <?= esc($category->color); ?>">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="title" style="background-color: <?= esc($category->color); ?>">
                        <a href="<?= generateCategoryUrl($category); ?>">
                            <span class="title-text"><?= esc($category->name); ?></span>
                        </a>
                    </h3>
                    <?php if (!empty($subCategories)): ?>
                        <?= loadView('category/_block_tabs', ['category' => $category, 'subCategories' => $category->children]); ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="section-content <?= $blockClass; ?> position-relative">

                <div id="loader_<?= esc($category->id); ?>" class="category-block-loader d-none">
                    <div class="loader-spinner"></div>
                </div>

                <div class="tab-content" id="blockContentWrapper_<?= esc($category->id); ?>">
                    <div class="tab-pane fade show active block-inner-content"
                         id="<?= $contentContainerId; ?>"
                         role="tabpanel">

                        <div id="content_cat_<?= esc($category->id); ?>">
                            <?php if (!empty($blockMap[$category->block_type]) && !empty($categoryPosts)): ?>
                                <?= loadView("category/" . $blockMap[$category->block_type], [
                                        'category'      => $category,
                                        'categoryPosts' => $categoryPosts,
                                        'hasSidebar'    => $hasSidebar
                                ]); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>