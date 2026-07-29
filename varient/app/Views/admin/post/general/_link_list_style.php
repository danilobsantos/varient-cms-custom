<?php
$cssListStyles = getAppDefault('cssListStyles');
$listStyle1 = getPostListStyle($post, 1);
$listStyle2 = getPostListStyle($post, 2);
$listStyle3 = getPostListStyle($post, 3);
?>

<div class="fv-row">
    <label class="form-label"><?= trans('link_list_style'); ?></label>
    <div class="separator separator-dashed my-2 mb-6"></div>

    <div class="row">
        <div class="col-sm-12 col-md-4 mb-6">
            <label class="form-label"><?= trans('level_1'); ?></label>
            <select name="link_list_style[1][style]" class="form-select" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="false" data-hide-search="true" required>
                <option></option>
                <?php foreach ($cssListStyles as $style): ?>
                    <option value="<?= esc($style); ?>" <?= (string)$style === (string)$listStyle1->style ? 'selected' : ''; ?>><?= esc($style); ?></option>
                <?php endforeach; ?>
            </select>
            <div class="mt-3">
                <?= formCheckbox('link_list_style[1][status]', (int)$listStyle1->status, trans("show_list_style_post_text")); ?>
            </div>
        </div>
        <div class="col-sm-12 col-md-4 mb-6">
            <label class="form-label"><?= trans('level_2'); ?></label>
            <select name="link_list_style[2][style]" class="form-select" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="false" data-hide-search="true" required>
                <option></option>
                <?php foreach ($cssListStyles as $style): ?>
                    <option value="<?= esc($style); ?>" <?= (string)$style === (string)$listStyle2->style ? 'selected' : ''; ?>><?= esc($style); ?></option>
                <?php endforeach; ?>
            </select>
            <div class="mt-3">
                <?= formCheckbox('link_list_style[2][status]', (int)$listStyle2->status, trans("show_list_style_post_text")); ?>
            </div>
        </div>
        <div class="col-sm-12 col-md-4 mb-6">
            <label class="form-label"><?= trans('level_3'); ?></label>
            <select name="link_list_style[3][style]" class="form-select" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="false" data-hide-search="true" required>
                <option></option>
                <?php foreach ($cssListStyles as $style): ?>
                    <option value="<?= esc($style); ?>" <?= (string)$style === (string)$listStyle3->style ? 'selected' : ''; ?>><?= esc($style); ?></option>
                <?php endforeach; ?>
            </select>
            <div class="mt-3">
                <?= formCheckbox('link_list_style[3][status]', (int)$listStyle3->status, trans("show_list_style_post_text")); ?>
            </div>
        </div>
    </div>
</div>