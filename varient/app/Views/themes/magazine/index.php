<?= $this->extend($viewsPath . '/layout'); ?>
<?= $this->section('content'); ?>

    <h1 class="title-index"><?= esc($homeTitle); ?></h1>

<?php if ((int)($config->featured_content_settings->br_news_status ?? 1) === 1 && countItems($breakingNews) > 0): ?>
    <section class="section section-newsticker">
        <div class="container-xl">
            <?= loadCommonView('post/_breaking_news', ['breakingNews' => $breakingNews]); ?>
        </div>
    </section>
<?php endif; ?>

<?php if ((int)$config->show_featured_section === 1):
    if ($activeTheme->theme == 'news') {
        echo loadView('partials/_featured_section_news');
    } else {
        echo loadView('partials/_featured_section');
    }
endif;

$catSliderIds = [];
$showAdCounter = 0;
if (!empty($categoryTree)):
    foreach ($categoryTree as $category):
        if ($category->show_on_homepage == 1 && $category->lang_id == $activeLang->id):
            $categoryPosts = $latestCategoryPosts[$category->id] ?? [];
            $subCategories = $category->children ?? [];
            $categoryWidgets = getCategoryWidgets($category->id, $widgets, $adSpaces, $activeLang->id);

            $blockView = 'category/_block_with_tabs';
            if ($category->block_type === 'block-6') {
                $blockView = 'category/_block_videos';
            }
            if ($category->block_type === 'block-5') {
                $blockView = 'category/_block_slider';
            }

            echo loadView($blockView, [
                    'category'        => $category,
                    'categoryPosts'   => $categoryPosts,
                    'subCategories'   => $subCategories,
                    'categoryWidgets' => $categoryWidgets
            ]);

            if ($category->block_type == 'block-5') {
                $catSliderIds[] = $category->id;
            }

            if ($showAdCounter == 0) {
                echo loadCommonView('partials/_ad_spaces', ['adSpace' => 'index_top', 'class' => 'mb-4']);
            }
            $showAdCounter++;
        endif;
    endforeach;

else:?>
    <div class="d-block h-100px w-100"></div>
<?php endif; ?>

<?php if (!empty($catSliderIds)): ?>
    <script>VrConfig.categorySliderIds = <?= json_encode($catSliderIds); ?>;</script>
<?php endif; ?>

<?= loadCommonView('partials/_ad_spaces', ['adSpace' => 'index_bottom', 'class' => 'mb-4']); ?>
<?= loadView('post/_latest_posts', ['latestPosts' => $latestPosts]); ?>

<?= $this->endSection(); ?>