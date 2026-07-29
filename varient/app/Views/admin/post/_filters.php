<?php
$postFormats = array_keys(getAppDefault('postFormats'));
$numFiltersApplied = 0;

$id = (int)inputGet('id');
$langId = (int)inputGet('lang_id');
$premiumContent = inputGet('premium_content');
$displayType = inputGet('display_type');
$postFormat = inputGet('post_format');
$categoryId = (int)inputGet('category_id');
$userId = (int)inputGet('user_id');
$searchParam = inputGet('q');

$selectedCategory = !empty($categoryId) ? getCategoryById($categoryId) : null;
$selectedUser = !empty($userId) ? getUserById($userId) : null;

$numFiltersApplied += !empty($id) && $id > 0 ? 1 : 0;
$numFiltersApplied += !empty($langId) ? 1 : 0;
$numFiltersApplied += !empty($postFormat) ? 1 : 0;
$numFiltersApplied += !empty($premiumContent) ? 1 : 0;
$numFiltersApplied += !empty($displayType) ? 1 : 0;
$numFiltersApplied += !empty($categoryId) ? 1 : 0;
$numFiltersApplied += !empty($userId) ? 1 : 0;
$numFiltersApplied += !empty($searchParam) ? 1 : 0;
?>

<div class="menu menu-sub menu-sub-dropdown w-600px mw-100" data-kt-menu="true">
    <div class="px-7 py-5">
        <div class="fs-5 text-gray-900 fw-bold"><?= trans("filter_options"); ?></div>
    </div>
    <div class="separator border-gray-200"></div>

    <div id="filterFormPosts" class="px-7 py-5" data-kt-subscription-table-filter="form">

        <div class="row">
            <div class="col-6">
                <div class="mb-6">
                    <label class="form-label fs-6 fw-semibold"><?= trans("id"); ?>:</label>
                    <div class="input-group input-group-solid mb-5">
                        <span class="input-group-text">#</span>
                        <input type="number" name="id" value="<?= $id > 0 ? esc($id) : ''; ?>" class="form-control form-control-solid" placeholder="E.g. 45" min="0" max="10000000000" step="1"/>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="mb-6">
                    <label class="form-label fs-6 fw-semibold"><?= trans("language"); ?>:</label>
                    <select name="lang_id" class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="true" data-hide-search="true">
                        <option></option>
                        <?php foreach ($activeLanguages as $language): ?>
                            <option value="<?= $language->id; ?>" <?= $langId == (int)$language->id ? 'selected' : ''; ?>><?= esc($language->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php if ($premiumMembership->status): ?>
                <div class="col-6">
                    <div class="mb-6">
                        <label class="form-label fs-6 fw-semibold"><?= trans("premium"); ?>:</label>
                        <select name="premium_content" class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="true" data-kt-subscription-table-filter="status" data-hide-search="true">
                            <option></option>
                            <?php if ($premiumMembership->subscriptionMode === 'selective' && $premiumMembership->subscriptionStatus): ?>
                                <option value="premium" <?= $premiumContent === 'premium' ? 'selected' : ''; ?>><?= trans("premium_content"); ?></option>
                            <?php endif; ?>
                            <?php if ($premiumMembership->exclusiveSaleStatus): ?>
                                <option value="exclusive" <?= $premiumContent === 'exclusive' ? 'selected' : ''; ?>><?= trans("exclusive_content"); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-6">
                <div class="mb-6">
                    <label class="form-label fs-6 fw-semibold"><?= trans("display_type"); ?>:</label>
                    <select name="display_type" class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="true" data-kt-subscription-table-filter="status" data-hide-search="true">
                        <option></option>
                        <?php foreach ($postSelectionOptions as $type): ?>
                            <option value="<?= esc($type); ?>" <?= $displayType === $type ? 'selected' : ''; ?>><?= trans($type, true); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-6">
                <div class="mb-6">
                    <label class="form-label fs-6 fw-semibold"><?= trans("post_format"); ?>:</label>
                    <select name="post_format" class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="true" data-kt-subscription-table-filter="status" data-hide-search="true">
                        <option></option>
                        <?php foreach ($postFormats as $format): ?>
                            <option value="<?= esc($format); ?>" <?= $postFormat === (string)$format ? 'selected' : ''; ?>><?= trans($format, true); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-6">
                <div class="mb-6">
                    <label class="form-label fs-6 fw-semibold"><?= trans("category"); ?>:</label>
                    <select name="category_id" class="form-select form-select-solid select2-categories" data-control="select2" data-hide-search="true"
                            data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-dropdown-parent="#filterFormPosts">
                        <?php if (!empty($selectedCategory)): ?>
                            <option value="<?= esc($selectedCategory->id); ?>"><?= esc($selectedCategory->name); ?></option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <?php if (hasPermission('manage_all_posts')): ?>
                <div class="col-6">
                    <div class="mb-6">
                        <label class="form-label fs-6 fw-semibold"><?= trans("user"); ?>:</label>
                        <select name="user_id" class="form-select form-select-solid select2-users" data-control="select2" data-hide-search="true"
                                data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-dropdown-parent="#filterFormPosts">
                            <?php if (!empty($selectedUser)): ?>
                                <option value="<?= esc($selectedUser->id); ?>"><?= esc($selectedUser->username); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-6">
                <div class="mb-10">
                    <label class="form-label fs-6 fw-semibold"><?= trans("search"); ?>:</label>
                    <input type="text" name="q" class="form-control form-control-solid" value="<?= $searchParam ?? ''; ?>" placeholder="<?= trans("title", "attr"); ?>...">
                </div>
            </div>
        </div>


        <div class="d-flex justify-content-end">
            <a href="<?= adminUrl('posts'); ?>" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6"><?= trans("reset"); ?></a>
            <button type="submit" class="btn btn-primary fw-semibold px-6" data-kt-menu-dismiss="true" data-kt-subscription-table-filter="filter"><?= trans("apply"); ?></button>
        </div>

    </div>
</div>

<button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
    <i class="ki-duotone ki-filter fs-2">
        <span class="path1"></span>
        <span class="path2"></span>
    </i><?= trans("filter"); ?>
    <?php if ($numFiltersApplied > 0): ?>
        &nbsp;&nbsp;<span class="badge badge-primary fw-bold fs-7"><?= $numFiltersApplied; ?></span>
    <?php endif; ?>
</button>