<?php
$buttonText = trans('add_post');
if (!empty($post)) {
    $buttonText = trans('save_changes');

    if ((int)$post->status === 0) {
        $buttonText = trans('publish');
    }
}
?>

<div class="d-flex justify-content-end">
    <div class="d-flex align-items-center gap-3">
        <input type="hidden" name="save_as_draft" id="formInputDraft" value="1">
        <input type="hidden" name="publish_post" id="formPublishInput" value="1">

        <?php if (!empty($post)): ?>

            <?php if ((int)$post->status === 0): ?>
                <button type="submit" class="btn btn-warning btn-post-submit" data-kt-indicator="off" onclick="document.getElementById('formPublishInput').value = 1;">
                    <span class="indicator-label"><?= trans("publish"); ?></span>
                    <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary btn-post-submit" data-kt-indicator="off" onclick="document.getElementById('formPublishInput').value = 0;">
                <span class="indicator-label"><?= trans('save_changes'); ?></span>
                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>

        <?php else: ?>

            <button type="submit" class="btn btn-secondary btn-post-submit" data-kt-indicator="off" onclick="document.getElementById('formInputDraft').value = 1;">
                <span class="indicator-label"><?= trans("save_draft"); ?></span>
                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>

            <button type="submit" class="btn btn-primary btn-post-submit" data-kt-indicator="off" onclick="document.getElementById('formInputDraft').value = 0;">
                <span class="indicator-label"><?= $buttonText; ?></span>
                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>

        <?php endif; ?>
    </div>
</div>