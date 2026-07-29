<?php
$answerId = $answer->id ?? 'ans_' . uniqid();
$answerImageUrl = !empty($answer->image_small) ? getStorageFileUrl($answer->image_small, $answer->storage) : '';
$imageIdValue = esc($answer->image_id ?? '');
$imagePreviewId = 'answer-image-preview-' . $answerId;
$imageInputId = 'input-answer-image-' . $answerId;
$answerText = esc($answer->answer_text ?? '');
$isCorrect = !empty($answer) && (int)$answer->is_correct === 1 ? 1 : 0;
$classAnswerWrapper = $answerFormat === 'text' ? 'answer-wrapper-text col-12 mb-3' : '';
$classAnswerCard = $answerFormat !== 'text' ? 'answer-card-fixed' : '';
?>

<div id="answer_card_<?= $answerId; ?>" class="answer-wrapper <?= $classAnswerWrapper; ?>">
    <div class="answer-card <?= $classAnswerCard; ?>">
        <button type="button" class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary btn-delete-quiz-answer w-25px h-25px bg-body shadow" data-id="<?= $answerId; ?>">
            <i class="ki-outline ki-cross fs-3 p-0"></i>
        </button>

        <div class="answer-card-body">
            <div class="d-flex justify-content-center image-upload-area p-3">
                <div class="image-input image-input-outline mt-3">
                    <div class="image-input-wrapper image-input-placeholder shadow-sm w-200px h-200px">
                        <img src="<?= $answerImageUrl; ?>" id="<?= $imagePreviewId; ?>" class="w-100 h-100 object-fit-cover"/>
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

                    <input type="hidden" name="questions[<?= esc($questionId); ?>][answers][<?= esc($answerId); ?>][image_id]" id="<?= $imageInputId; ?>" value="<?= $imageIdValue; ?>">
                </div>
            </div>

            <textarea name="questions[<?= esc($questionId); ?>][answers][<?= esc($answerId); ?>][answer_text]" class="answer-input" placeholder="<?= trans("answer_text"); ?>..."><?= $answerText; ?></textarea>

        </div>

        <div class="answer-card-footer">
            <?php if ($postFormat === 'personality_quiz'): ?>
                <div class="fv-row w-100">
                    <select name="questions[<?= esc($questionId); ?>][answers][<?= esc($answerId); ?>][assigned_result_id]" class="form-select quiz-result-select" data-selected-id="<?= esc($answer->assigned_result_id ?? ''); ?>" required></select>
                </div>
            <?php else:
                if ($postFormat !== 'poll'):?>
                    <div class="form-check form-check-custom form-check-success form-check-solid">
                        <input type="radio" name="questions[<?= esc($questionId); ?>][is_correct_answer_id]" value="<?= esc($answerId); ?>" class="form-check-input" id="rd_<?= $answerId; ?>" <?= $isCorrect ? 'checked' : ''; ?> required/>
                        <label class="form-check-label text-gray-600" for="rd_<?= $answerId; ?>"><?= trans("correct"); ?></label>
                    </div>
                <?php endif;
            endif; ?>

        </div>
    </div>
</div>