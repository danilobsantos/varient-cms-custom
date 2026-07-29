<div class="card card-flush py-4">
    <div class="card-header">
        <div class="card-title">
            <h2><?= !empty($videoType) && $videoType === 'recipe' ? trans("recipe_video") : trans("video"); ?></h2>
        </div>
    </div>
    <div class="card-body pt-5">
        <div class="d-flex flex-column gap-6">

            <ul class="nav nav-pills nav-justified justify-content-center mb-3" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-gray-900 text-active-white <?= empty($postVideo) ? 'active' : ''; ?>" id="pills-url-tab" data-bs-toggle="pill" data-bs-target="#pills-url" type="button" role="tab" aria-controls="pills-url" aria-selected="true">
                        <i class="ki-duotone ki-fasten fs-4 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i><?= trans("via_url"); ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-gray-900 text-active-white <?= !empty($postVideo) ? 'active' : ''; ?>" id="pills-local-tab" data-bs-toggle="pill" data-bs-target="#pills-local" type="button" role="tab" aria-controls="pills-local" aria-selected="false">
                        <i class="ki-duotone ki-file-up fs-4 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i><?= trans("upload"); ?>
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade <?= empty($postVideo) ? 'show active' : ''; ?>" id="pills-url" role="tabpanel" aria-labelledby="pills-url-tab">

                    <div class="fv-row mb-6">
                        <label class="form-label"><?= trans('video_url'); ?>&nbsp;<small class="text-muted fw-normal">(YouTube, Vimeo, Dailymotion, Instagram)</small></label>
                        <input type="text" name="video_url" id="inputVideoUrl" class="form-control" placeholder="<?= trans("video_url", "attr"); ?>" value="<?= esc(old('video_url', $post->video_url ?? '')); ?>">
                        <button type="button" id="btnGetVideo" class="btn btn-primary justify-content-center w-100 mt-2"><?= trans("get_video"); ?></button>
                    </div>

                    <div class="fv-row mb-6">
                        <label class="form-label"><?= trans('video_embed_code'); ?></label>
                        <textarea class="form-control form-control-solid font-monospace min-h-100px" name="video_embed_code" id="videoEmbedCode" placeholder="<?= trans("video_embed_code", "attr"); ?>"><?= esc(old('video_embed_code', $post->video_embed_code ?? '')); ?></textarea>
                    </div>

                    <div id="videoPreviewContainer" class="preview-area embed-video-preview-container">
                        <?php if (!empty($post) && !empty($post->video_embed_code)): ?>
                            <iframe id="video-preview-iframe" src="<?= esc($post->video_embed_code); ?>" class="w-100 h-175px rounded-1 shadow-sm border-0" allowfullscreen loading="lazy"></iframe>
                        <?php endif; ?>
                    </div>

                </div>

                <div class="tab-pane fade <?= !empty($postVideo) ? 'show active' : ''; ?>" id="pills-local" role="tabpanel" aria-labelledby="pills-local-tab">

                    <div class="mb-6 fv-row">
                        <button type="button" class="btn btn-sm btn-light-info"
                                data-bs-toggle="modal"
                                data-bs-target="#fileManagerModal"
                                data-modal-title="<?= trans("videos", "attr"); ?>"
                                data-source="video"
                                data-select-mode="single"
                                data-view-mode="file"
                                data-allowed-extensions="<?= esc(getAllowedExtensionsBySource('video')); ?>"
                                data-target-video-preview="#localVideoPreviewContainer"
                                data-target-input="#input-video-id">
                            <i class="ki-duotone ki-file-up fs-2"><span class="path1"></span><span class="path2"></span></i><?= trans("select_video"); ?>
                        </button>
                        <input type="hidden" name="video_id" id="input-video-id" value="<?= !empty($postVideo) ? $postVideo->id : ''; ?>">
                    </div>

                    <div id="localVideoPreviewContainer" class="preview-area">
                        <?php if (!empty($postVideo) && !empty($postVideo->file_path)):
                            $videoUrl = getStorageFileUrl($postVideo->file_path, $postVideo->storage); ?>
                            <div class="video-preview-container">
                                <button type="button" class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow btn-delete-video" aria-label="Remove">×</button>
                                <video width="320" height="240" style="width: 100%; height: 175px;" controls>
                                    <source src="<?= esc($videoUrl); ?>" type="video/mp4">
                                    <source src="<?= esc($videoUrl); ?>" type="video/ogg">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->section('scripts'); ?>
<script>
    $(document).ready(function () {
        const AJAX_ENDPOINT = '<?= base_url('Post/fetchVideoFromURL'); ?>';
        const videoType = '<?= !empty($videoType) && $videoType === 'recipe' ? 'recipe' : ''; ?>';

        $('#btnGetVideo').on('click', function () {
            var $btn = $(this);
            var $urlInput = $('#inputVideoUrl');
            var videoUrl = $urlInput.val().trim();
            var $embedTextarea = $('#videoEmbedCode');
            var $imageInput = $('#post-external-image-url');
            var $previewContainer = $('#videoPreviewContainer');

            $('.video-fetch-error').remove();
            $urlInput.removeClass('is-invalid');

            if (videoUrl === '') {
                $urlInput.addClass('is-invalid');

            }
            // Set Loading State
            var originalBtnText = $btn.text();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>&nbsp;' + VrConfig.text.processing);
            // Reset Output Fields
            $embedTextarea.val('');
            $imageInput.val('');
            $previewContainer.hide().empty();

            $.ajax({
                url: AJAX_ENDPOINT,
                type: 'POST',
                data: {url: videoUrl},
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        var data = response.data;

                        if (data.embed_code) {
                            // Update the form field
                            $embedTextarea.val(data.embed_code);

                            // Construct the HTML block with the dynamic src
                            var previewHtml =
                                '<iframe id="video-preview-iframe" src="' + data.embed_code + '" ' +
                                'class="w-100 h-175px rounded-2 shadow-sm border-0" allowfullscreen loading="lazy"></iframe>';

                            // Inject the constructed HTML and display the container
                            $previewContainer.html(previewHtml).show();
                        }

                        if (data.image && videoType !== 'recipe') {
                            $imageInput.val(data.image);
                            $('#post-image-preview').attr('src', data.image);
                        }

                        $urlInput.addClass('is-valid');
                        setTimeout(function () {
                            $urlInput.removeClass('is-valid');
                        }, 2000);

                    } else {
                        validationError($btn, response.error || 'Could not fetch video data.');
                    }
                },
                error: function (xhr, status, error) {
                    console.log(error);
                    var errorMessage = "An error occurred while connecting to the server.";
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    showError($btn, errorMessage);
                },
                complete: function () {
                    $btn.prop('disabled', false).text(originalBtnText);
                }
            });
        });

        function showError($element, message) {
            $element.after('<div class="alert alert-danger mt-2 video-fetch-error py-2">' + message + '</div>');
        }

        // Clear preview when URL input is cleared manually
        $('#inputVideoUrl').on('input', function () {
            if ($(this).val() === '') {
                $('#videoEmbedCode').val('');
                $('#post-external-image-url').val('');
                $('#videoPreviewContainer').hide().empty();
                $('.video-fetch-error').remove();
            }
        });
    });

    // Delete video
    $(document).on('click', '.btn-delete-video', function (e) {
        $('#localVideoPreviewContainer').empty();
        $('#input-video-id').val('');
    });
</script>
<?= $this->endSection(); ?>