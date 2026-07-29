<?php
$visibleLimit = 5;
$visibleSubCats = array_slice($subCategories, 0, $visibleLimit);
$dropdownSubCats = array_slice($subCategories, $visibleLimit);
$hasDropdown = !empty($dropdownSubCats);
$parentId = $category->id;
$parentBlockType = $category->block_type;
$targetTabId = '#tabCategoryContent_' . esc($parentId);
$targetControlsId = 'tabCategoryContent_' . esc($parentId);
?>

<ul class="nav nav-tabs nav-category-block" role="tablist">
    <li class="nav-item d-none d-lg-block" role="presentation">
        <button type="button" class="nav-link active ajax-category-tab bg-transparent border-0" id="tab-all-<?= esc($parentId); ?>" aria-controls="<?= $targetControlsId; ?>" aria-selected="true"
                data-bs-toggle="tab" data-bs-target="<?= $targetTabId; ?>" data-is-all="true" data-block-type="<?= esc($parentBlockType); ?>" data-category-id="<?= esc($parentId); ?>" role="tab">
            <?= trans("all"); ?>
        </button>
    </li>

    <?php foreach ($visibleSubCats as $subCategory):
        if ((int)$subCategory->show_on_homepage === 1): ?>
            <li class="nav-item d-none d-lg-block" role="presentation">
                <button type="button" class="nav-link ajax-category-tab bg-transparent border-0" id="tab-cat-<?= esc($subCategory->id); ?>" aria-controls="<?= $targetControlsId; ?>" aria-selected="false"
                        data-bs-toggle="tab" data-bs-target="<?= $targetTabId; ?>" data-block-type="<?= esc($parentBlockType); ?>" data-category-id="<?= esc($subCategory->id); ?>" role="tab">
                    <?= esc($subCategory->name); ?>
                </button>
            </li>
        <?php endif;
    endforeach; ?>

    <?php if ($hasDropdown): ?>
        <li class="nav-item dropdown d-none d-lg-block" role="presentation">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false" role="tab">
                <?= trans("more"); ?>
            </a>
            <ul class="dropdown-menu vr-scrollbar" role="presentation">
                <?php foreach ($dropdownSubCats as $subCategory):
                    if ((int)$subCategory->show_on_homepage === 1): ?>
                        <li role="presentation">
                            <button type="button" class="dropdown-item ajax-category-tab" aria-controls="<?= $targetControlsId; ?>" aria-selected="false" data-bs-toggle="tab"
                                    data-bs-target="<?= $targetTabId; ?>" data-block-type="<?= esc($parentBlockType); ?>" data-category-id="<?= esc($subCategory->id); ?>" role="tab">
                                <?= esc($subCategory->name); ?>
                            </button>
                        </li>
                    <?php endif;
                endforeach; ?>
            </ul>
        </li>
    <?php endif; ?>

    <li class="nav-item dropdown d-lg-none" role="presentation">
        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false" role="tab">
            <?= trans("more"); ?>
        </a>
        <ul class="dropdown-menu vr-scrollbar" role="presentation">
            <li role="presentation">
                <button type="button" class="dropdown-item ajax-category-tab" aria-controls="<?= $targetControlsId; ?>" aria-selected="true" data-bs-toggle="tab"
                        data-bs-target="<?= $targetTabId; ?>" data-is-all="true" data-block-type="<?= esc($parentBlockType); ?>" data-category-id="<?= esc($parentId); ?>" role="tab">
                    <?= trans("all"); ?>
                </button>
            </li>

            <?php foreach ($subCategories as $subCategory):
                if ((int)$subCategory->show_on_homepage === 1): ?>
                    <li role="presentation">
                        <button type="button" class="dropdown-item ajax-category-tab" aria-controls="<?= $targetControlsId; ?>" aria-selected="false" data-bs-toggle="tab"
                                data-bs-target="<?= $targetTabId; ?>" data-block-type="<?= esc($parentBlockType); ?>" data-category-id="<?= esc($subCategory->id); ?>" role="tab">
                            <?= esc($subCategory->name); ?>
                        </button>
                    </li>
                <?php endif;
            endforeach; ?>
        </ul>
    </li>
</ul>