<?php
$resultId = $result->id ?? 'r_' . uniqid();
$resultOrder = $result->result_order ?? (($numItems ?? 0) + 1);
$resultImageUrl = !empty($result->image_small) ? getStorageFileUrl($result->image_small, $result->storage) : '';
$imagePreviewId = 'result-image-preview-' . $resultId;
$imageInputId = 'input-result-image-' . $resultId;
$titleValue = esc($result->result_title ?? '');
$contentValue = esc($result->description ?? '');
$imageIdValue = esc($result->image_id ?? '');
$minCorrectCount = esc($result->min_correct_count ?? '');
$maxCorrectCount = esc($result->max_correct_count ?? '');
$collapsedClass = !empty($result) ? 'collapsed' : 'show';
?>

<div id="result_card_<?= $resultId; ?>" class="card card-flush card-list-item mb-4">
    <div class="card-header collapsible min-h-65px py-0 cursor-pointer rotate flex-wrap flex-md-nowrap">
        <h3 class="card-title flex-grow-1 align-items-center fs-5 fw-bold m-0 w-100 w-md-auto py-4 py-md-1 <?= $collapsedClass; ?>" data-bs-toggle="collapse" data-bs-target="#result_<?= $resultId; ?>">
            <i class="ki-duotone ki-check-square fs-2"><span class="path1"></span><span class="path2"></span></i>&nbsp;&nbsp;<span class="item-title-label"><?= $titleValue; ?></span>
        </h3>

        <div class="card-toolbar flex-nowrap gap-4">
            <input type="number" name="results[<?= $resultId ?>][result_order]" value="<?= esc($resultOrder); ?>" class="form-control form-control-solid w-80px h-35px" min="1" max="99999">

            <button type="button" class="btn btn-light-danger justify-content-center btn-delete-quiz-result w-35px h-35px" data-id="<?= $resultId; ?>">
                <i class="ki-duotone ki-trash fs-2 p-0"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
            </button>

            <div class="rotate-180 <?= $collapsedClass; ?>" data-bs-toggle="collapse" data-bs-target="#result_<?= $resultId; ?>">
                <i class="ki-duotone ki-down fs-1"></i>
            </div>
        </div>
    </div>

    <div id="result_<?= $resultId; ?>" class="collapse <?= $collapsedClass; ?>">
        <div class="card-body pt-0">
            <div class="mb-6">
                <label class="form-label"><?= trans("title"); ?></label>
                <input type="text" class="form-control item-title-input input-quiz-result-title" name="results[<?= $resultId ?>][result_title]" value="<?= $titleValue; ?>"
                       placeholder="<?= trans("title", "attr"); ?>" data-result-id="<?= esc($resultId, 'attr'); ?>" maxlength="500">
            </div>

            <div class="d-flex flex-wrap flex-md-nowrap gap-6">
                <div class="d-flex flex-column w-200px">
                    <label class="form-label"><?= trans("image"); ?></label>
                    <div class="d-flex mb-6">
                        <div class="image-input image-input-outline">
                            <div class="image-input-wrapper shadow-sm image-input-placeholder w-190px h-190px">
                                <img src="<?= $resultImageUrl; ?>" id="<?= $imagePreviewId; ?>" class="w-100 h-100 object-fit-cover"/>
                            </div>

                            <button type="button" class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                    data-bs-toggle="modal"
                                    data-bs-target="#fileManagerModal"
                                    data-modal-title="<?= trans("quiz_images", "attr"); ?>"
                                    data-source="quiz_image"
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

                            <input type="hidden" name="results[<?= $resultId ?>][image_id]" id="<?= $imageInputId; ?>" value="<?= $imageIdValue; ?>">
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column flex-grow-1">
                    <label class="form-label"><?= trans('content'); ?></label>
                    <?= view('admin/includes/_text_editor', [
                            'edtName'     => 'results[' . $resultId . '][description]',
                            'edtValue'    => $contentValue,
                            'edtImage'    => false,
                            'edtAIWriter' => false,
                            'edtClass'    => 'tinyMCEsmall'
                    ]); ?>
                </div>
            </div>

            <?php if (!empty($postFormat) && $postFormat == 'trivia_quiz'): ?>
                <div class="d-flex flex-column">
                    <label class="form-label"><?= trans("number_of_correct_answers"); ?>&nbsp;<span class="text-gray-500 mt-1 fw-semibold fs-6">(<?= trans("number_of_correct_answers_range"); ?>)</span></label>
                    <div class="d-flex gap-6">
                        <input type="number" name="results[<?= $resultId ?>][min_correct_count]" value="<?= esc($minCorrectCount); ?>" class="form-control w-200px" placeholder="<?= trans("min"); ?>" min="0">
                        <input type="number" name="results[<?= $resultId ?>][max_correct_count]" value="<?= esc($maxCorrectCount); ?>" class="form-control w-200px" placeholder="<?= trans("max"); ?>" min="0">
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>