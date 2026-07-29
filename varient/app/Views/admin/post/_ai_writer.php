<div class="modal fade" id="modalAiWriter" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h2 class="fw-bold"><?= trans("ai_content_generator"); ?></h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>

            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="form_ai_writer" class="form" action="#">

                    <input type="hidden" name="ai_language" value="<?= esc($activeLang->name); ?>">

                    <div id="aiStepInput">
                        <div class="row g-5">

                            <?php if (!empty($showGenerateWithAi)): ?>
                                <div class="col-12 fv-row">
                                    <label class="required form-label"><?= trans('generation_type'); ?></label>
                                    <select class="form-select form-select-solid" name="ai_generate_type" data-control="select2" data-hide-search="true">
                                        <option value="all" selected><?= trans("generation_type_1"); ?></option>
                                        <option value="content_only"><?= trans("generation_type_2"); ?></option>
                                    </select>
                                </div>
                            <?php else: ?>
                                <div class="col-12 fv-row">
                                    <label class="required form-label"><?= trans('generation_type'); ?></label>
                                    <select class="form-select form-select-solid" name="ai_generate_type" data-control="select2" data-hide-search="true">
                                        <option value="content_and_title" selected><?= trans("generation_type_3"); ?></option>
                                        <option value="content_only"><?= trans("generation_type_2"); ?></option>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <div class="col-md-6 fv-row">
                                <label class="required form-label"><?= trans('tone_style'); ?></label>
                                <select class="form-select form-select-solid" name="ai_tone" data-control="select2" data-hide-search="true">
                                    <?php if (!empty($aiWriter->tones)): ?>
                                        <?php foreach ($aiWriter->tones as $tone): ?>
                                            <option value="<?= $tone; ?>" <?= ($tone == 'neutral') ? 'selected' : ''; ?>>
                                                <?= trans('tone_' . $tone); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-6 fv-row">
                                <label class="required form-label"><?= trans('length_of_text'); ?></label>
                                <select class="form-select form-select-solid" name="ai_length" data-control="select2" data-hide-search="true">
                                    <?php if (!empty($aiWriter->lengths)): ?>
                                        <?php foreach ($aiWriter->lengths as $length): ?>
                                            <option value="<?= $length; ?>" <?= ($length == 'medium') ? 'selected' : ''; ?>>
                                                <?= trans($length); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-12 fv-row mb-5">
                                <label class="required form-label"><?= trans('topic'); ?></label>
                                <textarea class="form-control form-control-solid" rows="3" name="ai_topic" placeholder="<?= trans("topic_ai_placeholder", "attr") ?>"></textarea>
                                <div class="fs-7 fw-semibold text-muted mt-2">
                                    <?= trans("topic_ai_exp"); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="aiStepResult" class="d-none">

                        <div class="div-result mb-5">
                            <label class="fw-bold fs-6 mb-2 text-muted"><?= trans("title"); ?></label>
                            <input type="text" class="form-control form-control-solid" id="aiResultTitle" readonly>
                        </div>

                        <div class="div-result mb-5">
                            <label class="fw-bold fs-6 mb-2 text-muted"><?= trans('summary'); ?> & <?= trans("description"); ?></label>
                            <textarea class="form-control form-control-solid" id="aiResultSummary" rows="2" readonly></textarea>
                        </div>

                        <div class="div-result mb-5">
                            <label class="fw-bold fs-6 mb-2 text-muted"><?= trans("keywords"); ?></label>
                            <input type="text" class="form-control form-control-solid" id="aiResultKeywords" readonly>
                        </div>

                        <div class="div-result mb-5">
                            <label class="fw-bold fs-6 mb-2 text-muted"><?= trans("content"); ?></label>
                            <div class="bg-light p-4 rounded border border-dashed border-gray-300 scroll-y h-300px" id="aiResultContent" style="font-size: 15px; line-height: 26px;"></div>
                            <textarea id="aiResultContentRaw" class="d-none"></textarea>
                        </div>

                    </div>

                    <div class="d-flex justify-content-center gap-3 pt-8 ai-action-buttons">

                        <button type="button" id="btnAiGenerate" class="btn btn-primary gap-1 min-w-150px">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="18" height="18" fill="currentColor">
                                <path d="M295.4 37L310.2 73.8L347 88.6C350 89.8 352 92.8 352 96C352 99.2 350 102.2 347 103.4L310.2 118.2L295.4 155C294.2 158 291.2 160 288 160C284.8 160 281.8 158 280.6 155L265.8 118.2L229 103.4C226 102.2 224 99.2 224 96C224 92.8 226 89.8 229 88.6L265.8 73.8L280.6 37C281.8 34 284.8 32 288 32C291.2 32 294.2 34 295.4 37zM142.7 105.7L164.2 155.8L214.3 177.3C220.2 179.8 224 185.6 224 192C224 198.4 220.2 204.2 214.3 206.7L164.2 228.2L142.7 278.3C140.2 284.2 134.4 288 128 288C121.6 288 115.8 284.2 113.3 278.3L91.8 228.2L41.7 206.7C35.8 204.2 32 198.4 32 192C32 185.6 35.8 179.8 41.7 177.3L91.8 155.8L113.3 105.7C115.8 99.8 121.6 96 128 96C134.4 96 140.2 99.8 142.7 105.7zM496 368C502.4 368 508.2 371.8 510.7 377.7L532.2 427.8L582.3 449.3C588.2 451.8 592 457.6 592 464C592 470.4 588.2 476.2 582.3 478.7L532.2 500.2L510.7 550.3C508.2 556.2 502.4 560 496 560C489.6 560 483.8 556.2 481.3 550.3L459.8 500.2L409.7 478.7C403.8 476.2 400 470.4 400 464C400 457.6 403.8 451.8 409.7 449.3L459.8 427.8L481.3 377.7C483.8 371.8 489.6 368 496 368zM492 64C503 64 513.6 68.4 521.5 76.2L563.8 118.5C571.6 126.4 576 137 576 148C576 159 571.6 169.6 563.8 177.5L475.6 265.7L374.3 164.4L462.5 76.2C470.4 68.4 481 64 492 64zM76.2 462.5L340.4 198.3L441.7 299.6L177.5 563.8C169.6 571.6 159 576 148 576C137 576 126.4 571.6 118.5 563.8L76.2 521.5C68.4 513.6 64 503 64 492C64 481 68.4 470.4 76.2 462.5z"/>
                            </svg>
                            <span class="indicator-label">
                                <?= trans("generate_content"); ?>
                            </span>
                            <span class="indicator-progress gap-1">
                                <?= trans("generating_content_dots"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>

                        <div id="aiResultButtons" class="d-none d-flex gap-3">

                            <button type="button" class="btn btn-light-danger gap-2" id="btnAiReset">
                                <i class="ki-duotone ki-arrows-circle fs-2"><span class="path1"></span><span class="path2"></span></i>
                                <?= trans("reset"); ?>
                            </button>

                            <button type="button" class="btn btn-light-primary" id="btnAiRegenerate">
                                <span class="indicator-label gap-2">
                                    <i class="ki-duotone ki-arrows-loop fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    <?= trans("regenerate"); ?>
                                </span>
                                <span class="indicator-progress">
                                    <span class="spinner-border spinner-border-sm align-middle"></span>
                                </span>
                            </button>

                            <button type="button" class="btn btn-success gap-2" id="btnAiUse">
                                <i class="ki-duotone ki-check-circle fs-2"><span class="path1"></span><span class="path2"></span></i>
                                <?= trans("use_content"); ?>
                            </button>
                        </div>

                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->section('scripts'); ?>
<script>
    const AiWriterConfig = {
        endpoints: {
            generate: "<?= base_url('Post/generateAiPost'); ?>"
        },
        translations: {
            errorTopic: <?= json_encode(trans("msg_topic_empty", "raw")); ?>,
            successMsg: <?= json_encode(trans("msg_content_generated", "raw")); ?>,
            errorGeneric: <?= json_encode(trans("msg_error", "raw")); ?>,
            errorGenerate: <?= json_encode(trans("ai_writer_api_error", "raw")); ?>
        },
        selectors: {
            targetTitle: '.ai-input-title',
            targetSummary: '.ai-input-summary',
            targetKeywords: '.ai-input-keywords',
            targetContent: '.ai-input-content'
        }
    };
</script>
<script src="<?= base_url('assets/admin/js/ai-writer.js'); ?>"></script>
<?= $this->endSection(); ?>