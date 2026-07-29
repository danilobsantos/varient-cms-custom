<?php
$postImgId = !empty($post) ? $post->image_id : null;
$postImgURL = !empty($post) ? getPostImageUrl($post, 'mid') : null;
$imageIdsString = !empty($postImages) ? implode(',', array_column($postImages, 'id')) : '';
?>

<div class="card card-flush py-4">
    <div class="card-header">
        <div class="card-title">
            <h2><?= trans("image"); ?></h2>
        </div>
    </div>
    <div class="card-body pt-5">
        <div class="d-flex flex-column gap-6">

            <div class="fv-row mb-8">
                <div class="d-flex justify-content-center">
                    <div class="image-input image-input-outline">

                        <div class="image-input-wrapper image-input-placeholder w-200px h-200px">
                            <img src="<?= $postImgURL ?? ''; ?>" id="post-image-preview" class="w-100 h-100 object-fit-cover"/>
                        </div>

                        <button type="button" class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                data-bs-toggle="modal"
                                data-bs-target="#fileManagerModal"
                                data-modal-title="<?= trans("images", "attr"); ?>"
                                data-source="image"
                                data-select-mode="single"
                                data-view-mode="image"
                                data-allowed-extensions="<?= esc(getAllowedExtensionsBySource('image')); ?>"
                                data-target-input="#input-post-image"
                                data-target-preview="#post-image-preview"
                                data-kt-image-input-action="change"
                                data-bs-dismiss="click">
                            <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i>
                        </button>

                        <?php if (!empty($postImgId)): ?>
                            <button type="button" class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                    data-kt-image-input-action="remove"
                                    data-bs-dismiss="click"
                                    onclick="$('#input-post-image').val('');$('#post-external-image-url').val(''); $('#post-image-preview').attr('src',''); $(this).hide();">
                                <i class="ki-outline ki-cross fs-3"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <input type="hidden" name="image_id" id="input-post-image" value="<?= $postImgId ?? ''; ?>">
            </div>

            <div class="fv-row">
                <label class="form-label"><?= trans('external_image_url'); ?></label>
                <input type="text" name="image_url" id="post-external-image-url" class="form-control" placeholder="<?= trans("external_image_url", "attr"); ?>" value="<?= esc(old('image_url', $post->image_url ?? '')); ?>">
            </div>

            <?php if (in_array($postFormat, ['article', 'event', 'recipe', 'table_of_contents'])): ?>
                <div class="fv-row">
                    <label class="form-label"><?= trans('image_description'); ?></label>
                    <input type="text" name="image_description" class="form-control" placeholder="<?= trans("image_description", "attr"); ?>" value="<?= esc(old('image_description', $post->image_description ?? '')); ?>">
                </div>
            <?php endif; ?>

            <?php if (in_array($postFormat, ['article', 'recipe', 'event'])): ?>
                <div class="fv-row">
                    <label class="form-label mb-0"><?= trans('additional_images'); ?></label>
                    <div class="text-muted fs-7 mb-3"><?= trans("more_main_images"); ?></div>
                    <button type="button" class="btn btn-sm btn-light-info"
                            data-bs-toggle="modal"
                            data-bs-target="#fileManagerModal"
                            data-modal-title="<?= trans("images", "attr"); ?>"
                            data-source="image"
                            data-select-mode="multi"
                            data-view-mode="image"
                            data-allowed-extensions="<?= esc(getAllowedExtensionsBySource('image')); ?>"
                            data-target-preview-container="#additional-images-preview"
                            data-target-csv-input="#additional-images-ids">
                        <i class="ki-duotone ki-file-up fs-2"><span class="path1"></span><span class="path2"></span></i><?= trans("select_image"); ?>
                    </button>
                    <input type="hidden" name="additional_image_ids" id="additional-images-ids" value="<?= esc($imageIdsString); ?>">
                    <div id="additional-images-preview" class="d-flex flex-wrap mt-6 gap-4">

                        <?php if (!empty($postImages)):
                            foreach ($postImages as $image): ?>
                                <div class="fm-preview-item" data-id="<?= esc($image->id); ?>">
                                    <img src="<?= getStorageFileUrl($image->image_mid, $image->storage); ?>" alt="" class="img-thumbnail">
                                    <button type="button" class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow fm-preview-delete-btn" aria-label="Remove">×</button>
                                </div>
                            <?php endforeach;
                        endif; ?>

                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
