<?php
$listItemId = $postItem->id ?? 'n_' . uniqid();
$itemOrder = $postItem->item_order ?? ($numItems + 1 ?? 1);

$itemImageUrl = '';
if (!empty($postItem)) {
    $imageField = $postFormat === 'recipe' ? $postItem->image_default : $postItem->image_mid;
    $itemImageUrl = !empty($imageField) ? getStorageFileUrl($imageField, $postItem->storage) : '';
}

$imagePreviewId = 'post-image-preview-' . $listItemId;
$imageInputId = 'input-post-image-' . $listItemId;

$sidebarSizeClass = $postFormat === 'recipe' ? 'w-200px' : 'w-250px';
$titleValue = esc($postItem->title ?? '');
$contentValue = esc($postItem->content ?? '');
$imageInputSizeClass = $postFormat === 'recipe' ? 'w-190px h-190px' : 'w-175px h-175px';
$imageIdValue = esc($postItem->image_id ?? '');
$imageDescValue = esc($postItem->image_description ?? '');
$parentLinkNum = !empty($postItem->parent_link_num) ? esc($postItem->parent_link_num) : '';
$collapsedClass = !empty($postItem) ? 'collapsed' : '';
$showClass = empty($postItem) ? 'show' : '';
$edtImage = $postFormat === 'recipe' ? false : true;
$edtAIWriter = $postFormat === 'recipe' ? false : true;

$fileManagerSource = $postFormat === 'recipe' ? 'recipe_image' : 'image';
$fileManagerTitle = $postFormat === 'recipe' ? trans("recipe_images") : trans("images");
?>

<div id="list_item_card_<?= $listItemId; ?>" class="card card-flush card-list-item mb-5">
    <div class="card-header collapsible min-h-20px py-2 cursor-pointer rotate flex-wrap flex-md-nowrap">
        <h3 class="card-title flex-grow-1 align-items-center fs-5 fw-bold w-100 w-md-auto <?= $collapsedClass; ?>" data-bs-toggle="collapse" data-bs-target="#list_item_<?= $listItemId; ?>">
            <i class="bi bi-square-fill fs-10"></i>&nbsp;&nbsp;<span class="item-title-label"><?= $titleValue; ?></span>
        </h3>

        <div class="card-toolbar flex-nowrap gap-4">
            <input type="number" name="items[<?= $listItemId ?>][item_order]" value="<?= esc($itemOrder); ?>" class="form-control form-control-solid w-80px h-35px" min="1" max="99999">

            <button type="button" class="btn btn-light-danger justify-content-center btn-remove-list-item w-35px h-35px" data-id="<?= $listItemId; ?>">
                <i class="ki-duotone ki-trash fs-2 p-0"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
            </button>

            <div class="rotate-180 <?= $collapsedClass; ?>" data-bs-toggle="collapse" data-bs-target="#list_item_<?= $listItemId; ?>">
                <i class="ki-duotone ki-down fs-1"></i>
            </div>
        </div>
    </div>

    <div id="list_item_<?= $listItemId; ?>" class="collapse <?= $showClass; ?>">
        <div class="card-body pt-0">
            <?php if (in_array($postFormat, ['table_of_contents'])): ?>
                <div class="row">
                    <div class="col-sm-12 col-md-9 mb-6">
                        <label class="form-label"><?= trans("title"); ?></label>
                        <input type="text" class="form-control item-title-input ai-input-title" name="items[<?= $listItemId ?>][title]" value="<?= $titleValue; ?>" placeholder="<?= trans("title", "attr"); ?>" maxlength="500">
                    </div>
                    <div class="col-sm-12 col-md-3 mb-6">
                        <label class="form-label"><?= trans("parent_link"); ?></label>
                        <input type="number" class="form-control" name="items[<?= $listItemId ?>][parent_link_num]" value="<?= $parentLinkNum; ?>" placeholder="<?= trans("example"); ?>: 1" min="0" max="99999">
                    </div>
                </div>
            <?php else: ?>
                <div class="mb-6">
                    <label class="form-label"><?= trans("title"); ?></label>
                    <input type="text" class="form-control item-title-input ai-input-title" name="items[<?= $listItemId ?>][title]" value="<?= $titleValue; ?>" placeholder="<?= trans("title", "attr"); ?>" maxlength="500">
                </div>
            <?php endif; ?>

            <div class="d-flex flex-wrap flex-md-nowrap gap-6">
                <div class="d-flex flex-column <?= $sidebarSizeClass; ?>">
                    <label class="form-label"><?= trans("image"); ?></label>
                    <div class="d-flex mb-6">
                        <div class="image-input image-input-outline">
                            <div class="image-input-wrapper shadow-sm image-input-placeholder <?= $imageInputSizeClass; ?>">
                                <img src="<?= $itemImageUrl; ?>" id="<?= $imagePreviewId; ?>" class="w-100 h-100 object-fit-cover"/>
                            </div>

                            <button type="button" class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                    data-bs-toggle="modal"
                                    data-bs-target="#fileManagerModal"
                                    data-modal-title="<?= esc($fileManagerTitle); ?>"
                                    data-source="<?= esc($fileManagerSource); ?>"
                                    data-select-mode="single"
                                    data-view-mode="image"
                                    data-allowed-extensions="<?= esc(getAllowedExtensionsBySource('image')); ?>"
                                    data-target-input="#<?= $imageInputId; ?>"
                                    data-target-preview="#<?= $imagePreviewId; ?>"
                                    data-kt-image-input-action="change"
                                    data-bs-dismiss="click">
                                <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i>
                            </button>

                            <?php if (!empty($imageIdValue)): ?>
                                <button type="button" class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="remove"
                                        data-bs-dismiss="click"
                                        onclick="document.getElementById('<?= $imageInputId ?>').value=''; document.getElementById('<?= $imagePreviewId ?>').src=''; this.style.display='none';">
                                    <i class="ki-outline ki-cross fs-3"></i>
                                </button>
                            <?php endif; ?>

                            <input type="hidden" name="items[<?= $listItemId ?>][image_id]" id="<?= $imageInputId; ?>" value="<?= $imageIdValue; ?>">
                        </div>
                    </div>

                    <?php if ($postFormat !== 'recipe'): ?>
                        <input type="text" name="items[<?= $listItemId ?>][image_description]" value="<?= $imageDescValue; ?>" class="form-control" placeholder="<?= trans("image_description", "attr"); ?>">
                    <?php endif; ?>
                </div>

                <div class="d-flex flex-column flex-grow-1">
                    <label class="form-label"><?= trans('content'); ?></label>
                    <?= view('admin/includes/_text_editor', [
                            'edtId'       => 'editor_' . uniqid(),
                            'edtName'     => 'items[' . $listItemId . '][content]',
                            'edtValue'    => $contentValue,
                            'edtImage'    => $edtImage,
                            'edtAIWriter' => $edtAIWriter,
                            'edtClass'    => 'tinyMCEsmall'
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>