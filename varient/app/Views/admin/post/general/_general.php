<div class="card card-flush py-4">
    <div class="card-header">
        <div class="card-title">
            <h2><?= trans("general"); ?></h2>
        </div>
    </div>

    <div class="card-body pt-5">
        <div class="mb-6 fv-row">
            <label class="required form-label"><?= trans('title'); ?></label>
            <input type="text" name="title" class="form-control mb-2 ai-input-title" placeholder="<?= trans('title', "attr"); ?>" value="<?= esc(old('title', $post->title ?? '')); ?>" required>
            <?= validationError('title'); ?>
        </div>

        <div class="mb-6 fv-row">
            <?php $hidePermalink = !empty($post) && (int)$post->status === 1 && (int)$post->is_scheduled === 0 && (int)$post->visibility === 1 ? 0 : 1; ?>
            <?= view('admin/includes/_slug_input', ['dbItem' => $post ?? null, 'hidePermalink' => $hidePermalink]); ?>
        </div>

        <div class="mb-6 fv-row">
            <label class="form-label"><?= trans('summary'); ?> & <?= trans("description"); ?></label>
            <textarea name="summary" class="form-control min-h-65px ai-input-summary" placeholder="<?= trans('summary'); ?> & <?= trans("description"); ?>"><?= esc(old('summary', $post->summary ?? '')); ?></textarea>
        </div>

        <div class="mb-6 fv-row">
            <label class="form-label"><?= trans('tags'); ?></label>
            <input type="text" name="tags" id="inputTags" class="form-control mb-2 ai-input-keywords" placeholder="<?= trans("type_tag", "attr"); ?>" value="<?= !empty($tagsStr) ? $tagsStr : ''; ?>" maxlength="500">
        </div>

        <?php if (in_array($postFormat, ['article', 'audio', 'video', 'recipe', 'event'])): ?>
            <div class="mb-6 fv-row">
                <div id="main_editor">
                    <label class="form-label"><?= trans('content'); ?></label>
                    <?= view('admin/includes/_text_editor', [
                            'edtId'       => 'editor-ai-input-content',
                            'edtName'     => 'content',
                            'edtValue'    => old('content', $post->content ?? ''),
                            'edtImage'    => true,
                            'edtAIWriter' => true
                    ]); ?>
                </div>
            </div>

            <div class="mb-6 fv-row">
                <label class="form-label mb-0"><?= trans('attachments'); ?>&nbsp;(<?= trans("files"); ?>)</label>
                <div class="text-muted fs-7 mb-3"><?= trans("files_exp"); ?></div>
                <button type="button" class="btn btn-sm btn-light-info"
                        data-bs-toggle="modal"
                        data-bs-target="#fileManagerModal"
                        data-modal-title="<?= trans("files", "attr"); ?>"
                        data-source="file"
                        data-select-mode="multi"
                        data-view-mode="file"
                        data-allowed-extensions="<?= esc(getAllowedExtensionsBySource('file')); ?>"
                        data-target-list-container="#attachments-list"
                        data-target-csv-input="#input-attachments-ids">
                    <i class="ki-duotone ki-file-up fs-2"><span class="path1"></span><span class="path2"></span></i><?= trans("select_file"); ?>
                </button>
                <input type="hidden" name="attachment_ids" id="input-attachments-ids" value="<?= !empty($attachmentIdsString) ? esc($attachmentIdsString) : ''; ?>">

                <div id="attachments-list" class="attachments-list">
                    <?php if (!empty($attachments)):
                        foreach ($attachments as $attachment):?>
                            <li class="list-group-item list-group-item-sm fm-preview-item" data-id="<?= $attachment->id; ?>">
                                <span class="badge badge-secondary p-3"><?= esc($attachment->file_name); ?></span>
                                <button type="button" class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow fm-preview-delete-btn" aria-label="Remove">×</button>
                            </li>
                        <?php endforeach;
                    endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!in_array($postFormat, ['event'])): ?>
            <div class="mb-6 fv-row">
                <label class="form-label"><?= trans('optional_url'); ?></label>
                <input type="text" name="optional_url" class="form-control mb-2" placeholder="<?= trans("optional_url", "attr"); ?>" value="<?= esc(old('optional_url', $post->optional_url ?? '')); ?>">
                <div class="text-muted fs-7"><?= trans("optional_url_exp"); ?></div>
            </div>
        <?php endif; ?>

        <?php if (in_array($postFormat, ['table_of_contents'])): ?>
            <?= view("admin/post/general/_link_list_style"); ?>
        <?php endif; ?>

        <?php if (in_array($postFormat, ['recipe'])): ?>
            <?= view("admin/post/recipe/_recipe_data"); ?>
        <?php endif; ?>

        <?php if (in_array($postFormat, ['poll'])): ?>
            <div class="mb-6 fv-row">
                <label class="form-label"><?= trans('vote_permission'); ?></label>
                <?= formRadio('is_poll_public', ['0' => trans("registered_users_can_vote"), '1' => trans("all_users_can_vote")], (int)($post->is_poll_public ?? 1)); ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?= $this->section('scripts'); ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputElement = document.querySelector('#inputTags');
        const tagify = new Tagify(inputElement, {
            enforceWhitelist: false,
            whitelist: [],
            maxTags: <?= POST_TAGS_LIMIT; ?>,
            dropdown: {
                enabled: 1,
                position: 'text',
                closeOnSelect: false
            }
        });
        tagify.on('input', function (e) {
            const searchTerm = e.detail.value;
            if (searchTerm.length < 2) return;
            var data = {
                searchTerm: searchTerm
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Ajax/getTagSuggestions'),
                data: data,
                success: function (response) {
                    if (response.result == 1) {
                        tagify.settings.whitelist = response.tags;
                        tagify.dropdown.show(e.detail.value);
                    }
                }
            });
        });
    });
</script>

<?= $this->endSection(); ?>