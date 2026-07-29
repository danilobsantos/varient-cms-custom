<?php
$dataRecipe = $post->post_data;
$articleAd1 = null;
$articleAd2 = null;

if (!empty($adSpaces)) {
    foreach ($adSpaces as $item) {
        if ($item->ad_space == 'in_article_1') {
            $articleAd1 = $item;
        }
        if ($item->ad_space == 'in_article_2') {
            $articleAd2 = $item;
        }
    }
}

$paywallType = !empty($premiumMembership->paywallAppearance) ? $premiumMembership->paywallAppearance : 'fade';
?>

<div class="recipe-container">
    <div class="post-image">
        <?= loadCommonView('post/_post_image_preview', ['post' => $post, 'postImages' => $postImages]); ?>
    </div>

    <?php if (!$hasAccess && $paywallType === 'hard'): ?>
        <div class="mt-5 mb-5">
            <?= loadCommonView('premium/_paywall', [
                    'restrictionType' => $restrictionType,
                    'paywallType'     => 'hard'
            ]); ?>
        </div>
    <?php else: ?>

        <div class="container px-3 px-md-5">
            <div class="recipe-meta-card p-4 p-md-4">
                <div class="row text-center g-0">
                    <div class="col-md-3 col-6 meta-divider">
                        <div class="d-flex flex-column align-items-center">
                            <svg class="icon-md text-accent mb-2" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <span class="text-uppercase text-muted meta-label"><?= characterLimiter(trans("prep_time"), 15, '..'); ?></span>
                            <span class="fw-bold mt-1 meta-value"><?= esc($dataRecipe->prep_time ?? ''); ?> <?= trans("minute_short"); ?></span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 meta-divider">
                        <div class="d-flex flex-column align-items-center">
                            <svg class="icon-md text-accent mb-2" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"></path>
                            </svg>
                            <span class="text-uppercase text-muted meta-label"><?= characterLimiter(trans("cook_time"), 15, '..'); ?></span>
                            <span class="fw-bold mt-1 meta-value"><?= esc($dataRecipe->cook_time ?? ''); ?> <?= trans("minute_short"); ?></span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 meta-divider mt-4 mt-md-0">
                        <div class="d-flex flex-column align-items-center">
                            <svg class="icon-md text-accent mb-2" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span class="text-uppercase text-muted meta-label"><?= characterLimiter(trans("serving"), 15, '..'); ?></span>
                            <span class="fw-bold mt-1 meta-value"><?= esc($dataRecipe->serving ?? ''); ?></span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mt-4 mt-md-0">
                        <div class="d-flex flex-column align-items-center">
                            <?php $difficulty = $dataRecipe->difficulty ?? 0; ?>
                            <div class="d-flex align-items-center justify-content-center gap-1 mb-2" style="height: 1.5rem;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="<?= $difficulty >= 1 ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" class="<?= $difficulty >= 1 ? 'text-accent' : ''; ?>" style="<?= $difficulty < 1 ? 'color: var(--rc-border);' : ''; ?>" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                </svg>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="<?= $difficulty >= 2 ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" class="<?= $difficulty >= 2 ? 'text-accent' : ''; ?>" style="<?= $difficulty < 2 ? 'color: var(--rc-border);' : ''; ?>" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                </svg>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="<?= $difficulty >= 3 ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" class="<?= $difficulty >= 3 ? 'text-accent' : ''; ?>" style="<?= $difficulty < 3 ? 'color: var(--rc-border);' : ''; ?>" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                </svg>
                            </div>
                            <span class="text-uppercase text-muted meta-label"><?= trans("difficulty"); ?></span>
                            <span class="fw-bold mt-1 meta-value">
                                <?php
                                if ($difficulty == 1) {
                                    echo characterLimiter(trans("easy"), 15, '..');
                                } elseif ($difficulty == 2) {
                                    echo characterLimiter(trans("intermediate"), 15, '..');
                                } elseif ($difficulty == 3) {
                                    echo characterLimiter(trans("advanced"), 15, '..');
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!$hasAccess && $paywallType === 'fade'): ?>
            <div style="position: relative; overflow: hidden; min-height: 520px;">
        <?php endif; ?>

        <section class="row">
            <div class="col-12">
                <div class="entry-content mb-5">
                    <?= $post->content; ?>

                    <?php if (!empty($articleAd1)): ?>
                        <div class="mt-4 mb-3">
                            <?= loadCommonView('partials/_ad_spaces', ['adSpace' => 'in_article_1', 'class' => 'mb-3']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <?php if (!$hasAccess && $paywallType === 'fade'): ?>
            <?= loadCommonView('premium/_paywall', [
                    'restrictionType' => $restrictionType,
                    'paywallType'     => 'fade'
            ]); ?>
            </div>
        <?php else: ?>

            <section class="row gx-lg-5 ingredients-nutritional-container">
                <?php if (!empty($dataRecipe->ingredients)): ?>
                    <div class="col-lg-7 mb-5 mb-lg-0">
                        <h3 class="title-recipe-info">
                            <?= trans("ingredients"); ?>
                        </h3>

                        <div class="mb-4">
                            <ul class="ingredient-list">
                                <?php foreach ($dataRecipe->ingredients as $itemIngredient): ?>
                                    <li>
                                        <label class="d-flex align-items-start w-100 m-0" style="cursor: pointer;">
                                            <input type="checkbox" class="ingredient-checkbox d-none">
                                            <div class="custom-check-circle">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </div>
                                            <span class="ingredient-text"><?= esc($itemIngredient); ?></span>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($dataRecipe->nInfo)): ?>
                    <aside class="col-lg-5">
                        <div class="d-flex justify-content-end">
                            <div class="nutrition-card mb-5 p-4">
                                <h3 class="d-flex align-items-center title-recipe-info">
                                    <svg class="icon-md me-2 text-accent" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                    </svg>
                                    <?= trans("nutritional_information"); ?>
                                </h3>

                                <div class="nutrition-data">
                                    <?php foreach ($dataRecipe->nInfo as $itemInfo): ?>
                                        <div class="nutrition-row">
                                            <span class="text-muted"><?= esc($itemInfo->n ?? ''); ?></span>
                                            <span class="fw-bold"><?= esc($itemInfo->v ?? ''); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </aside>
                <?php endif; ?>
            </section>

            <?php if (!empty($post->video_url) || !empty($postVideo)): ?>
                <section class="row">
                    <div class="col-sm-12 my-4">
                        <h3 class="title-recipe-info">
                            <?= trans("recipe_video_title"); ?>
                        </h3>
                        <?= loadCommonView('post/_video_player', ['post' => $post]); ?>
                    </div>
                </section>
            <?php endif; ?>

            <section class="entry-content">
                <?php if (!empty($articleAd2)): ?>
                    <?= loadCommonView('partials/_ad_spaces', ['adSpace' => 'in_article_2', 'class' => 'mb-4']); ?>
                <?php endif; ?>

                <h3 class="title-recipe-info mt-5">
                    <?= trans("directions"); ?>
                </h3>

                <div class="recipe-directions">
                    <?php
                    $i = 1;
                    $recipeDirections = [];

                    if (!empty($postListItems)):
                        echo '<ul class="directions-list">';
                        foreach ($postListItems as $direction):
                            $directionImageUrl = '';
                            if ($direction->image_default) {
                                $directionImageUrl = getStorageFileUrl($direction->image_default, $direction->storage);
                            }

                            $stepData = [
                                    "@type" => "HowToStep",
                                    "name"  => $direction->title,
                                    "url"   => current_url(true) . "#step" . $i
                            ];

                            if (!empty($direction->content)) {
                                $stepData["text"] = strip_tags($direction->content);
                            }
                            if (!empty($directionImageUrl)) {
                                $stepData["image"] = $directionImageUrl;
                            }
                            $recipeDirections[] = $stepData;
                            ?>

                            <li id="step<?= $i; ?>" class="direction">
                                <h3 class="step-title">
                                    <?= $i; ?>.&nbsp;<?= esc($direction->title); ?>
                                </h3>

                                <?php if (!empty($direction->content)): ?>
                                    <div class="mt-2 step-description"><?= $direction->content; ?></div>
                                <?php endif; ?>

                                <?php if (!empty($direction->image_default)): ?>
                                    <div class="mt-3 d-flex justify-content-center">
                                        <img src="<?= esc($directionImageUrl); ?>" alt="<?= esc($direction->title); ?>" width="856" height="570" loading="lazy"/>
                                    </div>
                                <?php endif; ?>
                            </li>
                            <?php
                            $i++;
                        endforeach;
                        echo '</ul>';
                    endif;
                    ?>
                </div>
            </section>

        <?php endif; ?>
    <?php endif; ?>
</div>