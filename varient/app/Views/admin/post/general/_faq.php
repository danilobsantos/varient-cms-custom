<div class="card card-flush card-faq">
    <div class="card-header collapsible cursor-pointer rotate <?= !empty($post) ? 'collapsed' : 'active'; ?>" data-bs-toggle="collapse" data-bs-target="#faq_card_collapsible">
        <h3 class="card-title fw-bold fs-3"><?= trans("frequently_asked_questions"); ?>&nbsp;<span class="text-gray-500 fw-normal">(<?= trans("optional"); ?>)</span></h3>
        <div class="card-toolbar rotate-180">
            <i class="ki-duotone ki-down fs-1"></i>
        </div>
    </div>
    <div id="faq_card_collapsible" class="collapse <?= empty($post) ? 'show' : ''; ?>">
        <div class="card-body pt-5">

            <?php
            $title = $post->faq?->title ?? trans('frequently_asked_questions');
            $description = $post->faq?->description ?? '';
            ?>

            <div class="fw-row mb-6">
                <label class="form-label"><?= trans("title"); ?></label>
                <input type="text" class="form-control" name="faq_title" placeholder="<?= trans("title"); ?>" maxlength="255" value="<?= esc($title); ?>">
            </div>

            <div class="mb-4">
                <div id="post_faq_list" class="d-flex flex-column gap-3">
                    <?php if (!empty($post->faq) && !empty($post->faq->items)):
                        foreach ($post->faq->items as $item):
                            $itemId = uniqid(); ?>

                            <div class="faq-item-container animation-fade-in" id="faq_<?= $itemId; ?>">
                                <div class="faq-header d-flex align-items-center justify-content-between bg-gray-100 collapsed" data-bs-toggle="collapse" data-bs-target="#collapse_<?= $itemId; ?>" aria-expanded="false">
                                    <div class="d-flex align-items-center text-truncate pe-3">
                                        <i class="ki-duotone ki-right item-chevron me-3 fs-2"></i>
                                        <span class="fw-bold text-gray-800 fs-6" id="preview_<?= $itemId; ?>"><?= esc($item->q); ?></span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-icon btn-light-danger js-remove-faq">
                                        <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                    </button>
                                </div>
                                <div id="collapse_<?= $itemId; ?>" class="collapse" data-bs-parent="#post_faq_list">
                                    <div class="p-4 border-top border-light">
                                        <div class="mb-4">
                                            <label class="form-label"><?= trans("question"); ?></label>
                                            <input type="text" class="form-control fw-bold" name="faq[<?= $itemId; ?>][q]" onkeyup="updatePreview('<?= $itemId; ?>', this)" placeholder="<?= trans("question", "attr"); ?>"
                                                   value="<?= esc($item->q); ?>">
                                        </div>
                                        <div>
                                            <label class="form-label"><?= trans("answer"); ?></label>
                                            <textarea class="form-control" name="faq[<?= $itemId; ?>][a]" rows="3" placeholder="<?= trans("answer", "attr"); ?>"><?= esc($item->a); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach;
                    endif; ?>
                </div>
            </div>

            <button type="button" id="btn_add_faq_question" class="btn btn-secondary">
                <i class="ki-duotone ki-plus"></i><?= trans("add_question"); ?>
            </button>
        </div>
    </div>
</div>

<?= $this->section('scripts'); ?>
<script>
    (function () {
        const faqList = document.getElementById('post_faq_list');
        const labelNewQuestion = <?= json_encode(trans("new_question")); ?>;
        const labelQuestion = <?= json_encode(trans("question")); ?>;
        const labelAnswer = <?= json_encode(trans("answer")); ?>;

        const btnAdd = document.getElementById('btn_add_faq_question');
        if (btnAdd) {
            btnAdd.addEventListener('click', () => {
                const uniqueId = new Date().getTime();
                const html = `
                <div class="faq-item-container animation-fade-in" id="faq_${uniqueId}">
                    <div class="faq-header d-flex align-items-center justify-content-between bg-gray-100"
                         data-bs-toggle="collapse"
                         data-bs-target="#collapse_${uniqueId}"
                         aria-expanded="true">
                        <div class="d-flex align-items-center text-truncate pe-3">
                        <i class="ki-duotone ki-right item-chevron me-3 fs-2"></i>
                            <span class="fw-bold text-gray-800 fs-6" id="preview_${uniqueId}">${labelNewQuestion}</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-icon btn-light-danger js-remove-faq">
                            <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                        </button>
                    </div>
                    <div id="collapse_${uniqueId}" class="collapse show" data-bs-parent="#post_faq_list">
                        <div class="p-4 border-top border-light">
                            <div class="mb-4">
                                <label class="form-label">${labelQuestion}</label>
                                <input type="text" class="form-control fw-bold" name="faq[${uniqueId}][q]" onkeyup="updatePreview(${uniqueId}, this)" placeholder="${labelQuestion}">
                            </div>
                            <div>
                                <label class="form-label">${labelAnswer}</label>
                                <textarea class="form-control" name="faq[${uniqueId}][a]" rows="3" placeholder="${labelAnswer}"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            `;
                faqList.insertAdjacentHTML('beforeend', html);
                const newItemInput = document.querySelector(`#faq_${uniqueId} input[name*='[q]']`);
                if (newItemInput) newItemInput.focus();
            });
        }

        faqList.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('.js-remove-faq');

            if (removeBtn) {
                e.stopPropagation();
                const item = removeBtn.closest('.faq-item-container');
                if (item) {
                    item.remove();
                }
            }
        });

        window.updatePreview = function (id, input) {
            const previewEl = document.getElementById(`preview_${id}`);
            if (input.value.trim() !== "") {
                previewEl.textContent = input.value;
                previewEl.classList.remove('text-gray-500');
                previewEl.classList.add('text-gray-700');
            } else {
                previewEl.textContent = labelNewQuestion;
                previewEl.classList.add('text-gray-500');
                previewEl.classList.remove('text-gray-700');
            }
        };
    })();
</script>
<?= $this->endSection(); ?>