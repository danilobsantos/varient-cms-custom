<div class="card card-flush py-4">
    <div class="card-header">
        <div class="card-title">
            <h2><?= trans("audios"); ?></h2>
        </div>
    </div>
    <div class="card-body pt-5">
        <div class="d-flex flex-column gap-6">

            <div class="fv-row">
                <span class="text-muted"><?= trans("audios_exp"); ?></span>
            </div>

            <div class="fv-row">
                <button type="button" class="btn btn-sm btn-light-info"
                        data-bs-toggle="modal"
                        data-bs-target="#fileManagerModal"
                        data-modal-title="<?= trans("audios", "attr"); ?>"
                        data-source="audio"
                        data-select-mode="multi"
                        data-view-mode="file"
                        data-allowed-extensions="<?= esc(getAllowedExtensionsBySource('audio')); ?>"
                        data-target-list-container="#audios-list"
                        data-target-csv-input="#input-audios-ids">
                    <i class="ki-duotone ki-file-up fs-2"><span class="path1"></span><span class="path2"></span></i><?= trans("select_audio"); ?>
                </button>
                <input type="hidden" name="audio_ids" id="input-audios-ids" value="<?= !empty($postAudios) ? implode(',', array_column($postAudios, 'id')) : ''; ?>">
            </div>

            <ul id="audios-list" class="list-group list-group-music scroll">
                <?php if (!empty($postAudios)):
                    foreach ($postAudios as $audio):?>
                        <li class="list-group-item d-flex align-items-center fm-preview-item px-0 py-4" data-id="<?= $audio->id; ?>">
                            <i class="bi bi-music-note-list me-3 fs-4 flex-shrink-0"></i>
                            <span class="flex-grow-1"><?= esc($audio->file_name); ?></span>
                            <button type="button" class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow-sm flex-shrink-0 fm-preview-delete-btn" aria-label="Remove">×</button>
                        </li>
                    <?php endforeach;
                endif; ?>
            </ul>
        </div>
    </div>
</div>