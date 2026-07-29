<?php
$eventDetails = $post->post_data?->details ?? null;
$speakersList = $post->post_data?->speakers ?? [];
$speakersTitle = $eventDetails?->speakers_title ?? trans('speakers_guests', 'raw');
?>

    <div class="card card-flush">
        <div class="card-header collapsible cursor-pointer rotate <?= !empty($post) ? 'collapsed' : 'active'; ?>" data-bs-toggle="collapse" data-bs-target="#speakers_card_collapsible">
            <h3 class="card-title fw-bold fs-3 gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#78829D" class="bi bi-people-fill" viewBox="0 0 16 16">
                    <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                </svg>
                <?= trans("speakers_guests"); ?>
            </h3>
            <div class="card-toolbar rotate-180">
                <i class="ki-duotone ki-down fs-1"></i>
            </div>
        </div>
        <div id="speakers_card_collapsible" class="collapse <?= empty($post) ? 'show' : ''; ?>">
            <div class="card-body pt-5">

                <div class="fw-row mb-6">
                    <label class="form-label"><?= trans("section_title"); ?></label>
                    <input type="text" class="form-control" name="event_speakers_title" placeholder="<?= trans("title"); ?>" maxlength="255" value="<?= esc($speakersTitle); ?>">
                </div>

                <div id="event-speakers-container" class="d-flex flex-wrap gap-3 mb-3">

                    <?php if (!empty($speakersList)):
                        foreach ($speakersList as $item):
                            $itemId = uniqid();
                            $imageInputId = 'input-speaker-image-' . $itemId;
                            $imagePreviewId = 'img-speaker-' . $itemId;
                            $avatarUrl = getUserAvatar($item->avatarPath ?? '', $item->storage ?? 'local'); ?>
                            <div class="d-flex justify-content-center align-items-center flex-column event-speaker">
                                <button type="button" class="btn btn-sm btn-icon flex-shrink-0 btn-light-danger btn-remove-speaker">
                                    <i class="ki-duotone ki-trash fs-5">
                                        <span class="path1"></span><span class="path2"></span>
                                        <span class="path3"></span><span class="path4"></span><span class="path5"></span>
                                    </i>
                                </button>

                                <div class="image-input image-input-outline mt-3">
                                    <div class="image-input-wrapper image-input-placeholder shadow-sm w-125px h-125px">
                                        <img src="<?= esc($avatarUrl); ?>" id="<?= $imagePreviewId; ?>" class="w-100 h-100 object-fit-cover"/>
                                    </div>

                                    <button type="button" class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                            data-bs-toggle="modal"
                                            data-bs-target="#fileManagerModal"
                                            data-modal-title="<?= trans("event_images", "attr"); ?>"
                                            data-source="event_avatar_image"
                                            data-select-mode="single"
                                            data-view-mode="image"
                                            data-allowed-extensions="jpg,jpeg,png,webp"
                                            data-target-input="#<?= $imageInputId; ?>"
                                            data-target-preview="#<?= $imagePreviewId; ?>"
                                            data-kt-image-input-action="change"
                                            data-bs-dismiss="click">
                                        <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i>
                                    </button>

                                    <input type="hidden" name="speakers[<?= $itemId; ?>][avatar]" id="<?= $imageInputId; ?>" value="<?= esc($item->avatarId ?? ''); ?>">
                                </div>

                                <input type="text" class="form-control mt-5" name="speakers[<?= $itemId; ?>][name]" value="<?= esc($item->name ?? ''); ?>" placeholder="<?= trans("name", "attr"); ?>" maxlength="255">
                                <input type="text" class="form-control mt-2" name="speakers[<?= $itemId; ?>][description]" value="<?= esc($item->description ?? ''); ?>" placeholder="<?= trans("description", "attr"); ?>" maxlength="255">
                            </div>
                        <?php endforeach;
                    endif; ?>
                </div>

                <div class="mb-6 fv-row">
                    <button type="button" id="btn-add-speakers" class="btn btn-sm btn-light-success">
                        <i class="ki-duotone ki-plus fs-4"></i>
                        <?= trans("add_new"); ?>
                    </button>
                </div>

            </div>
        </div>
    </div>

<?= $this->section('scripts'); ?>
    <script>
        $(document).ready(function () {
            var $speakersContainer = $('#event-speakers-container');
            $('#btn-add-speakers').on('click', function () {
                const newId = Date.now() + '-' + Math.floor(Math.random() * 1000);
                var newOptionHTML = `
                 <div class="d-flex justify-content-center align-items-center flex-column event-speaker">
                        <button type="button" class="btn btn-sm btn-icon flex-shrink-0 btn-light-danger btn-remove-speaker">
                            <i class="ki-duotone ki-trash fs-5">
                                <span class="path1"></span><span class="path2"></span>
                                <span class="path3"></span><span class="path4"></span><span class="path5"></span>
                            </i>
                        </button>

                        <div class="image-input image-input-outline mt-3">
                            <div class="image-input-wrapper image-input-placeholder shadow-sm w-125px h-125px">
                                <img src="" id="img-speaker-${newId}" class="w-100 h-100 object-fit-cover"/>
                            </div>

                            <button type="button" class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                    data-bs-toggle="modal"
                                    data-bs-target="#fileManagerModal"
                                    data-modal-title="<?= trans("event_images", "attr"); ?>"
                                    data-source="event_avatar_image"
                                    data-select-mode="single"
                                    data-view-mode="image"
                                    data-allowed-extensions="jpg,jpeg,png,webp"
                                    data-target-input="#input-speaker-image-${newId}"
                                    data-target-preview="#img-speaker-${newId}"
                                    data-kt-image-input-action="change"
                                    data-bs-dismiss="click">
                                <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i>
                            </button>

                            <input type="hidden" name="speakers[${newId}][avatar]" id="input-speaker-image-${newId}" value="">
                        </div>

                        <input type="text" class="form-control mt-5" name="speakers[${newId}][name]" placeholder="<?= trans("name", "js"); ?>" maxlength="255">
                        <input type="text" class="form-control mt-2" name="speakers[${newId}][description]" placeholder="<?= trans("description", "js"); ?>" maxlength="255">
                    </div>`;
                $speakersContainer.append(newOptionHTML);
            });

            $(document).on('click', '.btn-remove-speaker', function () {
                Swal.fire(swalOptions(VrConfig.text.confirmDelete)).then((result) => {
                    if (result.isConfirmed) {
                        $(this).closest('.event-speaker').remove();
                    }
                });
            });
        });
    </script>
<?= $this->endSection(); ?>