<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$selectedLangId = old('lang_id') ?? $image?->lang_id ?? $activeLang?->id;
$selectedAlbumId = old('album_id') ?? ($image->album_id ?? 0);
$selectedCategoryId = old('category_id') ?? ($image->category_id ?? 0);
$imageUrl = !empty($image->path_small) && !empty($image->storage) ? getStorageFileUrl($image->path_small, $image->storage) : '';
?>

<form action="<?= $action ?>" method="post" enctype="multipart/form-data" class="form kt-form d-flex flex-column flex-xl-row gap-5 gap-xl-7 gap-xxl-10">
    <?= csrf_field(); ?>

    <?php if (!empty($image)): ?>
        <input type="hidden" name="id" value="<?= esc($image->id); ?>">
    <?php endif; ?>

    <?php if (!empty(inputGet('redirect_url'))): ?>
        <input type="hidden" name="redirect_url" value="<?= esc(inputGet('redirect_url')); ?>">
    <?php endif; ?>

    <div class="w-100 flex-xl-row-auto w-xl-300px w-xxl-325px">
        <div class="card card-flush py-4">
            <div class="card-header">
                <div class="card-title">
                    <h2><?= trans("options"); ?></h2>
                </div>
            </div>
            <div class="card-body pt-5">
                <div class="d-flex flex-column gap-8">

                    <div class="fv-row">
                        <label class="required form-label"><?= trans("language"); ?></label>
                        <select name="lang_id" class="form-select" onchange="getAlbumsByLang(this.value);" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                            <?php foreach ($activeLanguages as $language): ?>
                                <option value="<?= $language->id; ?>"<?= $selectedLangId == $language->id ? 'selected' : ''; ?>><?= esc($language->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="fv-row">
                        <label class="required form-label"><?= trans("album"); ?></label>
                        <select name="album_id" id="albums" class="form-select" onchange="getGalleryCategoriesByAlbum(this.value);" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" required>
                            <option></option>
                            <?php if (!empty($albums)):
                                foreach ($albums as $item):?>
                                    <option value="<?= $item->id; ?>" <?= $selectedAlbumId == $item->id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
                                <?php endforeach;
                            endif; ?>
                        </select>
                    </div>

                    <div class="fv-row">
                        <label class="form-label"><?= trans("category"); ?></label>
                        <select name="category_id" id="categories" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                            <option></option>
                            <?php if (!empty($categories)):
                                foreach ($categories as $item):?>
                                    <option value="<?= $item->id; ?>" <?= $selectedCategoryId == $item->id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
                                <?php endforeach;
                            endif; ?>
                        </select>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column flex-row-fluid gap-7 gap-xl-10">
        <div class="card card-flush py-4">
            <div class="card-header">
                <div class="card-title">
                    <h2><?= trans("general"); ?></h2>
                </div>
            </div>
            <div class="card-body pt-5">

                <div class="mb-6 fv-row">
                    <label class="form-label"><?= trans('title'); ?></label>
                    <input type="text" name="title" class="form-control mb-2" placeholder="<?= trans('title', 'attr'); ?>" value="<?= esc(old('title', $image->title ?? '')); ?>">
                </div>

                <?php if (!empty($image->path_small)): ?>
                    <div class="row mb-6">
                        <label class="col-12">
                            <label class="form-label"><?= trans('image'); ?></label>
                        </label>
                        <div class="col-12">
                            <?= view("admin/includes/_image_input", ['fnName' => 'file', 'fnImageUrl' => $imageUrl, 'fnDelete' => false, 'fnSizeClass' => 'w-200px h-200px']); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mb-6 fv-row">
                    <div class="fv-row">
                        <div class="dropzone" id="dropzoneJsGallery" <?= !empty($image->path_small) ? 'style="display: none;"' : ''; ?>>
                            <div class="dz-message needsclick">
                                <i class="ki-duotone ki-file-up fs-3x text-primary"><span class="path1"></span><span class="path2"></span></i>
                                <div class="ms-4">
                                    <h3 class="fs-5 fw-bold text-gray-900 mb-1"><?= trans("drop_files_or_click_upload"); ?></h3>
                                    <span class="fs-7 fw-semibold text-gray-500"><?= trans("upload_up_to_10_files"); ?></span>
                                </div>
                            </div>
                            <div class="uploaded-files"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                <span class="indicator-label"><?= !empty($image) ? trans("save_changes") : trans("add_image"); ?></span>
                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>
        </div>
    </div>
</form>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>

<script>
    Dropzone.autoDiscover = false;
    var dzContainer = document.getElementById("dropzoneJsGallery");
    var maxFilesAllowed = <?= !empty($image) ? 1 : 10; ?>;

    var dropzone = new Dropzone(dzContainer, {
        url: "<?= base_url('Gallery/uploadImage'); ?>",
        paramName: "file",
        maxFiles: maxFilesAllowed,
        maxFilesize: 30, // MB
        addRemoveLinks: true,
        params: {
            [VrConfig.csrfTokenName]: $('meta[name="X-CSRF-TOKEN"]').attr('content')
        },
        success: function (file, response) {
            appendDropzoneUploadedFile(file, response, dzContainer);
        },
        removedfile: function (file) {
            deleteDropzoneUploadedFile(file, dzContainer);
        }
    });

    $(document).ready(function () {
        $('#btnRemoveImage').on('click', function () {
            $('#existingImageContainer').hide();
            $('#dropzoneJsGallery').show();
        });
    });

</script>

<?= $this->endSection(); ?>
