<?php
$slug = $dbItem->slug ?? '';
$parentSlug = $dbItem->parent_slug ?? '';
$baseUrl = !empty($dbItem) && !empty($dbItem->lang_id) ? generateBaseURLByLangId($dbItem->lang_id) : base_url();

$permalink = !empty($parentSlug) ? $baseUrl . $parentSlug . '/' : $baseUrl;
$permalink .= $slug;
?>
    <label class="form-label"><?= trans('slug'); ?></label>
    <input type="text" name="slug" class="form-control mb-2" placeholder="<?= trans("slug", "attr"); ?>" value="<?= esc($slug); ?>">

<?php if (!empty($hidePermalink) || empty($slug)): ?>
    <div class="text-muted fs-7"><?= trans("slug_exp"); ?></div>
<?php else: ?>
    <div class="d-flex align-items-center mb-8 gap-2">
        <span class="text-gray-700 fw-semibold fs-6"><?= trans("permalink"); ?>:</span>
        <a href="<?= $permalink; ?>" target="_blank" class="fw-bold text-gray-500 text-hover-primary fs-6 text-truncate d-flex align-items-center">
            <?= $permalink; ?>
            <i class="ki-outline ki-arrow-up-right fs-3 text-gray-500 text-hover-primary"></i>
        </a>
    </div>
<?php endif; ?>