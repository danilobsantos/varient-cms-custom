<?php
if (empty($edtId)) {
    $edtId = 'editor_' . uniqid();
} ?>

<div class="d-flex align-items-center gap-2 mb-3">
    <?php if (!empty($edtImage)): ?>
        <button type="button" class="btn btn-sm btn-light-primary"
                data-bs-target="#fileManagerModal"
                data-modal-title="<?= trans("images", "attr"); ?>"
                data-select-mode="multi"
                data-source="image"
                data-editor-id="<?= esc($edtId); ?>">
            <i class="ki-duotone ki-picture fs-2"><span class="path1"></span><span class="path2"></span></i>
            <?= trans("add_image"); ?>
        </button>
    <?php endif; ?>

    <?php $edtAiClass = '';
    if ($aiWriter->status && !empty($edtAIWriter) && empty($showGenerateWithAi)):
        $edtAiClass = 'ai-input-content'; ?>
        <button type="button" class="btn btn-sm btn-light-primary btn-ai-writer-trigger" onclick="$('#modalAiWriter').modal('show');">
            <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
            <?= trans("ai_writer"); ?>
        </button>
    <?php endif; ?>

</div>

<textarea id="<?= esc($edtId); ?>" class="<?= !empty($edtClass) ? esc($edtClass) : 'tinyMCE'; ?> form-control <?= $edtAiClass; ?>" name="<?= $edtName ?? ''; ?>"><?= $edtValue ?? ''; ?></textarea>