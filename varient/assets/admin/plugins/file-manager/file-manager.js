$(document).ready(function () {

    // State Management
    let isMultiSelectEnabled = false;
    let isLoading = false;
    let currentPage = 1;
    let currentSearch = "";
    let hasMoreFiles = true;
    let myDropzoneInstance = null;

    const fileManagerBaseUrl = fileManagerConfig.baseUrl;

    // DOM References
    const $fileManagerModal = $('#fileManagerModal');
    const $editFileModal = $('#editFileModal');
    const $deleteConfirmModal = $('#deleteConfirmModal');
    const $bulkDeleteConfirmModal = $('#bulkDeleteConfirmModal');

    const gridView = $fileManagerModal.find('.fm-grid-view');
    const emptyState = $fileManagerModal.find('.fm-empty-state');
    const selectionFooter = $fileManagerModal.find('.fm-selection-footer');
    const modalLabel = $fileManagerModal.find('#fileManagerModalLabel');
    const uploadPanel = $fileManagerModal.find('.fm-upload-panel');
    const modalBody = $fileManagerModal.find('.modal-body');

    // Initial Setup
    applyConfigText();
    setupSearchListener();

    /**
     * Apply Config Text
     */
    function applyConfigText() {
        const txt = fileManagerConfig.text;
        modalLabel.text(txt.modalTitleDefault);
        $fileManagerModal.find('.fm-btn-upload').contents().filter(function () {
            return this.nodeType === 3;
        }).last().replaceWith(" " + txt.btnUpload);
        $fileManagerModal.find('.fm-empty-state-text-1').text(txt.emptyStateText1);
        $fileManagerModal.find('.fm-empty-state-text-2').text(txt.emptyStateText2);
        $fileManagerModal.find('.fm-loading-text').text(txt.loadingText);
        $fileManagerModal.find('.fm-btn-cancel-selection span').text(txt.btnCancel);
        $fileManagerModal.find('.fm-btn-select-item span').text(txt.btnSelect);
    }

    // Modal Trigger
    $(document).on('click', 'button[data-bs-target="#fileManagerModal"]', function () {
        const $this = $(this);
        const modalTitle = $this.data('modal-title');
        const selectMode = $this.data('select-mode');
        const viewMode = $this.data('view-mode') || 'image';
        const dataSource = $this.data('source') || 'image';
        const allowedExtensions = $this.data('allowed-extensions') || null;

        // Set modal data attributes for later use (e.g., in AJAX calls)
        $fileManagerModal.data('targetInput', null);
        $fileManagerModal.data('targetPreview', null);
        $fileManagerModal.data('targetPreviewContainer', null);
        $fileManagerModal.data('targetCsvInput', null);
        $fileManagerModal.data('targetListContainer', null);
        $fileManagerModal.data('targetVideoPreviewContainer', null);

        // Capture the target editor ID
        $fileManagerModal.data('targetEditorId', $this.data('editor-id'));

        $fileManagerModal.data('currentViewMode', viewMode);
        $fileManagerModal.data('currentDataSource', dataSource);
        $fileManagerModal.data('allowedExtensions', allowedExtensions);

        // Create Dropzone extension badges
        const $extensionsDiv = $fileManagerModal.find('.fm-dropzone-extensions');
        $extensionsDiv.empty();

        if (allowedExtensions) {
            const extensionsArray = allowedExtensions.split(',');
            extensionsArray.forEach(function (ext) {
                const trimmedExt = ext.trim();
                if (trimmedExt.length > 0) {
                    const $badge = $('<span></span>')
                        .addClass('badge bg-secondary text-gray-500 me-1')
                        .text(trimmedExt.toUpperCase());
                    $extensionsDiv.append($badge);
                }
            });
        }

        modalLabel.text(modalTitle);

        // Set state based on selection mode
        if (selectMode === 'multi') {
            isMultiSelectEnabled = true;
            $fileManagerModal.data('targetPreviewContainer', $this.data('target-preview-container'));
            $fileManagerModal.data('targetCsvInput', $this.data('target-csv-input'));
            $fileManagerModal.data('targetListContainer', $this.data('target-list-container'));
        } else {
            isMultiSelectEnabled = false;
            $fileManagerModal.data('targetInput', $this.data('target-input'));
            $fileManagerModal.data('targetPreview', $this.data('target-preview'));
            $fileManagerModal.data('targetVideoPreviewContainer', $this.data('target-video-preview'));
        }

        $fileManagerModal.modal('show');
    });

    // Infinite Scroll Listener
    modalBody.on('scroll', function () {
        if (isLoading || !hasMoreFiles) return;
        const scrollTop = $(this).scrollTop();
        const scrollHeight = $(this)[0].scrollHeight;
        const clientHeight = $(this).innerHeight();
        if (scrollTop + clientHeight >= scrollHeight - fileManagerConfig.loadThreshold) {
            loadMoreFiles();
        }
    });

    // Modal Mode: On Show
    $fileManagerModal.on('shown.bs.modal', function () {
        resetAndLoadFiles();
    });

    // Modal Mode: On Hide
    $fileManagerModal.on('hidden.bs.modal', function () {
        hideSelectionFooter();
        isMultiSelectEnabled = false;
        modalLabel.text(fileManagerConfig.text.modalTitleDefault);
        $fileManagerModal.find('.fm-search-input').val('');
        currentSearch = "";

        const $btnUpload = $fileManagerModal.find('.fm-btn-upload');
        if ($btnUpload.hasClass('active')) {
            uploadPanel.hide();
            $btnUpload.removeClass('active');
            $btnUpload.html('<i class="bi bi-upload me-1"></i> ' + fileManagerConfig.text.btnUpload);
        }

        if (myDropzoneInstance) {
            myDropzoneInstance.destroy();
            myDropzoneInstance = null;
        }

        currentPage = 1;
        isLoading = false;
        hasMoreFiles = true;
        $fileManagerModal.find('.fm-loading-indicator').hide();
        $fileManagerModal.find('.fm-grid-view .row').empty();

        $fileManagerModal.data('targetInput', null);
        $fileManagerModal.data('targetPreview', null);
        $fileManagerModal.data('targetPreviewContainer', null);
        $fileManagerModal.data('targetCsvInput', null);
        $fileManagerModal.data('targetListContainer', null);
        $fileManagerModal.data('targetVideoPreviewContainer', null);

        // Reset target editor ID
        $fileManagerModal.data('targetEditorId', null);

        $fileManagerModal.data('currentViewMode', 'image');
        $fileManagerModal.data('currentDataSource', 'image');
        $fileManagerModal.data('allowedExtensions', null);
        $fileManagerModal.find('.fm-dropzone-extensions').empty();
    });

    // Upload Panel Toggler
    $fileManagerModal.on('click', '.fm-btn-upload', function (e) {
        e.preventDefault();
        uploadPanel.slideToggle(300);
        const $this = $(this);
        $this.toggleClass('active');
        const txt = fileManagerConfig.text;
        if ($this.hasClass('active')) {
            $this.html('<i class="bi bi-x-lg me-1"></i> ' + txt.btnUploadDone);
            if (myDropzoneInstance === null) {
                initializeDropzone();
            }
        } else {
            $this.html('<i class="bi bi-upload me-1"></i> ' + txt.btnUpload);
        }
    });

    // Double Click to Select (Single Mode)
    $fileManagerModal.on('dblclick', '.fm-file-item', function () {
        if (!isMultiSelectEnabled) {
            const $this = $(this);
            const selectedFile = {
                id: $this.data('id'),
                name: $this.data('name'),
                url: $this.data('url'),
                alt: $this.data('alt')
            };
            const targetInput = $fileManagerModal.data('targetInput');
            const targetPreview = $fileManagerModal.data('targetPreview');
            const targetVideoPreviewContainer = $fileManagerModal.data('targetVideoPreviewContainer');
            const viewMode = $fileManagerModal.data('currentViewMode') || 'image';
            if (targetInput) {
                $(targetInput).val(selectedFile.id);
            }
            if (targetPreview) {
                if (viewMode === 'image' && selectedFile.url) {
                    $(targetPreview).attr('src', selectedFile.url).attr('alt', selectedFile.alt).show();
                    if ($('#post-external-image-url').length) {
                        $('#post-external-image-url').val('');
                    }
                } else {
                    $(targetPreview).hide();
                }
            }

            if (targetVideoPreviewContainer) {
                if (selectedFile.url) {
                    $(targetVideoPreviewContainer).html(`
                    <video width="320" height="240" style="width: 100%; height: 175px;" controls>
                        <source src="${selectedFile.url}" type="video/mp4">
                        <source src="${selectedFile.url}" type="video/ogg">
                        Your browser does not support the video tag.
                    </video>
                `);
                } else {
                    $(targetVideoPreviewContainer).hide();
                }
            }
            $fileManagerModal.find('[data-bs-dismiss="modal"]').first().click();
        }
    });

    // Edit Button Click
    $fileManagerModal.on('click', '.fm-btn-edit-item', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).closest('.fm-custom-actions').removeClass('active');

        const fileItem = $(this).closest('.fm-file-item');
        const fileId = fileItem.data('id');
        const fileName = fileItem.data('name');
        const fileAlt = fileItem.data('alt');
        const isDownloadable = fileItem.data('is-downloadable');

        const dataSource = $fileManagerModal.data('currentDataSource') || 'image';

        const $altTextGroup = $editFileModal.find('#fm-edit-file-alt').closest('.mb-3');
        if (dataSource === 'image' || dataSource === 'content_image') {
            $altTextGroup.show();
        } else {
            $altTextGroup.hide();
        }

        const $downloadSwitch = $editFileModal.find('input[type="checkbox"][name="is_downloadable"]');
        const $downloadSwitchGroup = $downloadSwitch.closest('.mb-3');

        if (dataSource === 'audio') {
            $downloadSwitchGroup.show();
            $downloadSwitch.prop('checked', isDownloadable == 1);
        } else {
            $downloadSwitchGroup.hide();
        }

        $editFileModal.find('#fm-edit-file-name').val(fileName);
        $editFileModal.find('#fm-edit-file-alt').val(fileAlt);
        $editFileModal.data('editing-id', fileId);
        $editFileModal.modal('show');
    });

    // Save Changes Button Click
    $editFileModal.on('click', '.fm-btn-save-edit', function () {
        const fileId = $editFileModal.data('editing-id');
        if (!fileId) return;
        const newName = $editFileModal.find('#fm-edit-file-name').val();
        const newAlt = $editFileModal.find('#fm-edit-file-alt').val();
        const $downloadSwitch = $editFileModal.find('input[type="checkbox"][name="is_downloadable"]');
        const newIsDownloadable = $downloadSwitch.is(':checked') ? 1 : 0;
        const dataSource = $fileManagerModal.data('currentDataSource') || 'image';

        let postData = {
            'file_name': newName,
            'alt_text': newAlt,
            'is_downloadable': newIsDownloadable,
            'source': dataSource
        };
        $.ajax({
            url: fileManagerBaseUrl + 'update/' + fileId,
            type: 'POST',
            data: postData,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="X-CSRF-TOKEN"]').attr('content')
            },
            success: function (response) {
                if (response.status === 'success') {
                    // Update the item data in the grid
                    const fileItemsToUpdate = $fileManagerModal.find('.fm-file-item[data-id="' + fileId + '"]');
                    fileItemsToUpdate.each(function () {
                        const $item = $(this);
                        $item.data('name', newName);
                        $item.data('alt', newAlt);
                        $item.data('is-downloadable', newIsDownloadable);
                        $item.attr('data-is-downloadable', newIsDownloadable);

                        $item.find('.fm-file-name').text(newName).attr('title', newName);
                        if ($item.find('.fm-file-thumbnail-grid').length > 0) {
                            $item.find('.fm-file-thumbnail-grid').attr('alt', newAlt);
                        }
                    });
                    $editFileModal.modal('hide');
                    // Show success notification
                    toastr.success(fileManagerConfig.text.msgUpdated, fileManagerConfig.text.success);
                } else {
                    var errorMessage = 'Update failed: ' + (response.message || 'Unknown error');
                    showErrorAlert(errorMessage);
                }
            },
            error: function (xhr) {
                var errorMessage = 'An error occurred: ' + (xhr.responseJSON ? xhr.responseJSON.message : xhr.responseText);
                showErrorAlert(errorMessage);
            }
        });
    });

    // Individual Delete Button Click
    $fileManagerModal.on('click', '.fm-btn-delete-item-individual', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).closest('.fm-custom-actions').removeClass('active');

        const fileItem = $(this).closest('.fm-file-item');
        const fileId = fileItem.data('id');
        const fileName = fileItem.data('name');

        // XSS Protection: Escape the filename before rendering as HTML
        const safeFileName = escapeHTML(fileName);

        const confirmText = fileManagerConfig.text.deleteConfirmBody.replace('{fileName}', safeFileName);
        $deleteConfirmModal.find('.fm-delete-confirm-body').html(confirmText);
        $deleteConfirmModal.data('deleting-id', fileId);
        $deleteConfirmModal.modal('show');
    });

    // Confirm Delete Button Click
    $deleteConfirmModal.on('click', '.fm-btn-confirm-delete', function () {
        const fileId = $deleteConfirmModal.data('deleting-id');
        if (!fileId) return;
        performDelete([fileId]);
    });

    // Footer: Select Button Click
    $fileManagerModal.on('click', '.fm-btn-select-item', function () {
        const selectedItems = $fileManagerModal.find('.fm-file-item.active');
        let selectedFiles = [];

        selectedItems.each(function () {
            const $item = $(this);
            const file = {
                id: $item.data('id'),
                name: $item.data('name'),
                url: $item.data('url'),
                urlOriginal: $item.data('url-original'),
                alt: $item.data('alt')
            };
            selectedFiles.push(file);
        });

        if (selectedFiles.length > 0) {
            if (isMultiSelectEnabled) {
                // Check if we are inserting into TinyMCE Editor
                const targetEditorId = $fileManagerModal.data('targetEditorId');

                if (targetEditorId) {
                    // Iterate through selected files and insert into TinyMCE
                    selectedFiles.forEach(file => {
                        // Use original URL if available (for editors), otherwise fallback to thumb/standard URL
                        const srcToUse = file.urlOriginal && file.urlOriginal !== 'undefined' ? file.urlOriginal : file.url;

                        if (srcToUse) {
                            const imgHtml = '<p><img src="' + srcToUse + '" alt="' + (file.alt || '') + '"/></p>';

                            // Ensure TinyMCE exists and the specific instance is available
                            if (typeof tinymce !== 'undefined' && tinymce.get(targetEditorId)) {
                                tinymce.get(targetEditorId).execCommand('mceInsertContent', false, imgHtml);
                            } else {
                                console.warn('TinyMCE editor instance not found: ' + targetEditorId);
                            }
                        }
                    });
                    // Close modal and exit without running standard CSV logic
                    $fileManagerModal.find('[data-bs-dismiss="modal"]').first().click();
                    return;
                }

                // Multi-select mode: update preview container and CSV input (Existing Logic)
                const targetPreviewContainer = $fileManagerModal.data('targetPreviewContainer');
                const targetCsvInput = $fileManagerModal.data('targetCsvInput');
                const targetListContainer = $fileManagerModal.data('targetListContainer');

                const $previewContainer = $(targetPreviewContainer);
                const $csvInput = $(targetCsvInput);
                const $listContainer = $(targetListContainer);

                if (!$csvInput.length) {
                    console.error('Multi-select target "data-target-csv-input" not set.');
                    $fileManagerModal.find('[data-bs-dismiss="modal"]').first().click();
                    return;
                }

                const existingCsv = $csvInput.val() || '';
                const existingIdsArray = (existingCsv.length > 0) ? existingCsv.split(',') : [];
                const existingIdsSet = new Set(existingIdsArray);
                const dataSource = $fileManagerModal.data('currentDataSource') || 'image';

                let allIds = existingIdsArray;
                let wasEmpty = (allIds.length === 0);

                if (wasEmpty) {
                    if ($previewContainer.length) $previewContainer.empty();
                    if ($listContainer.length) $listContainer.empty();
                }

                const deleteBtnHtml = `<button type="button" class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow-sm fm-preview-delete-btn" aria-label="Remove">×</button>`;

                selectedFiles.forEach(file => {
                    const fileIdStr = String(file.id);

                    if (!existingIdsSet.has(fileIdStr)) {
                        // Add to image preview container
                        if ($previewContainer.length && file.url) {
                            const $previewItem = $(
                                `<div class="fm-preview-item" data-id="${file.id}">
                                    <img src="${file.url}" alt="${file.alt}" class="img-thumbnail">
                                    ${deleteBtnHtml}
                                </div>`
                            );
                            $previewContainer.append($previewItem);
                        }
                        // Add to list preview container
                        if ($listContainer.length) {
                            const safeFileName = escapeHTML(file.name);
                            const safeFileExtension = getFileExtension(file.url)
                            let $listItem = '';
                            if (dataSource === 'audio') {
                                $listItem = $(
                                    `<li class="list-group-item d-flex align-items-center fm-preview-item px-0 py-4" data-id="${file.id}">
                                        <i class="bi bi-music-note-list me-3 fs-4 flex-shrink-0"></i>
                                        <span class="flex-grow-1">${safeFileName}.${safeFileExtension}</span>
                                        <button type="button" class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow-sm flex-shrink-0 fm-preview-delete-btn" aria-label="Remove">×</button>
                                    </li>`
                                );
                            } else {
                                $listItem = $(
                                    `<li class="list-group-item list-group-item-sm fm-preview-item" data-id="${file.id}">
                                    <span class="badge badge-secondary p-3">${safeFileName}.${safeFileExtension}</span>
                                    ${deleteBtnHtml}
                                </li>`
                                );
                            }

                            $listContainer.append($listItem);
                        }
                        allIds.push(fileIdStr);
                    }
                });
                $csvInput.val(allIds.join(','));

            } else {
                // Single-select mode: update target input and preview image
                const selectedFile = selectedFiles[0];
                const targetInput = $fileManagerModal.data('targetInput');
                const targetPreview = $fileManagerModal.data('targetPreview');
                const viewMode = $fileManagerModal.data('currentViewMode') || 'image';
                if (targetInput) {
                    $(targetInput).val(selectedFile.id);
                }
                if (targetPreview) {
                    if (viewMode === 'image' && selectedFile.url) {
                        $(targetPreview).attr('src', selectedFile.url).attr('alt', selectedFile.alt).show();
                        if ($('#post-external-image-url').length) {
                            $('#post-external-image-url').val('');
                        }
                    } else {
                        $(targetPreview).hide();
                    }
                }
            }
            $fileManagerModal.find('[data-bs-dismiss="modal"]').first().click();
        }
    });

    // Footer: Cancel Button Click
    $fileManagerModal.on('click', '.fm-btn-cancel-selection', function () {
        hideSelectionFooter();
    });

    // Footer: Delete Selection Button Click
    $fileManagerModal.on('click', '.fm-btn-delete-selection', function () {
        const selectedItems = $fileManagerModal.find('.fm-file-item.active');
        const count = selectedItems.length;
        if (count === 0) return;

        const fileNames = [];
        selectedItems.each(function () {
            fileNames.push(escapeHTML($(this).data('name')));
        });

        const confirmText = fileManagerConfig.text.bulkDeleteConfirmBody;
        $bulkDeleteConfirmModal.find('.fm-bulk-delete-confirm-body').html(confirmText + '<br><small class="text-muted">' + fileNames.join('<br>') + '</small>');
        $bulkDeleteConfirmModal.modal('show');
    });

    // Confirm Bulk Delete Button Click
    $bulkDeleteConfirmModal.on('click', '.fm-btn-confirm-bulk-delete', function () {
        const selectedItems = $fileManagerModal.find('.fm-file-item.active');
        if (selectedItems.length === 0) return;
        const ids = [];
        selectedItems.each(function () {
            ids.push($(this).data('id'));
        });
        performDelete(ids);
    });

    /**
     * XSS Protection: HTML Escape Function
     */
    function escapeHTML(str) {
        if (str === null || typeof str === 'undefined') {
            return '';
        }
        return $('<div>').text(str).html();
    }

    // Global listener for dynamic preview-delete buttons
    $(document).on('click', '.fm-preview-delete-btn', function () {
        const $this = $(this);
        const $previewItem = $this.closest('.fm-preview-item');
        if (!$previewItem.length) return;

        const fileIdToRemove = String($previewItem.data('id'));

        // Find the container (image grid or list)
        const $previewContainer = $previewItem.closest('[id]');
        if (!$previewContainer.length) return;

        // Find the original trigger button based on the container ID
        const previewContainerId = $previewContainer.attr('id');
        const $triggerButton = $(`button[data-target-preview-container="#${previewContainerId}"], button[data-target-list-container="#${previewContainerId}"]`);
        if (!$triggerButton.length) {
            console.error('Preview item delete failed: Could not find trigger button.');
            return;
        }

        // Find the CSV input from the trigger button's data
        const csvInputSelector = $triggerButton.data('target-csv-input');
        if (!csvInputSelector) {
            console.error('Preview item delete failed: Could not find CSV input target.');
            return;
        }

        // Update the CSV input value
        const $csvInput = $(csvInputSelector);
        const oldCsv = $csvInput.val() || '';
        let idsArray = (oldCsv.length > 0) ? oldCsv.split(',') : [];

        idsArray = idsArray.filter(id => id !== fileIdToRemove);

        $csvInput.val(idsArray.join(','));
        $previewItem.remove();
    });

    /**
     * Debounced search listener
     */
    function setupSearchListener() {
        let searchTimeout;
        $fileManagerModal.on('keyup', '.fm-search-input', function () {
            clearTimeout(searchTimeout);
            const searchTerm = $(this).val();
            searchTimeout = setTimeout(() => {
                const trimmedTerm = searchTerm.trim();
                if (trimmedTerm.length === 1) {
                    return;
                }
                if (trimmedTerm !== currentSearch) {
                    currentSearch = trimmedTerm;
                    resetAndLoadFiles();
                }
            }, 300);
        });
    }

    /**
     * Performs the AJAX call to delete one or more files
     */
    function performDelete(ids) {
        if (!ids || ids.length === 0) return;
        const dataSource = $fileManagerModal.data('currentDataSource') || 'image';
        let postData = {
            'ids': ids,
            'source': dataSource
        };

        $.ajax({
            url: fileManagerBaseUrl + 'delete',
            type: 'POST',
            data: postData,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="X-CSRF-TOKEN"]').attr('content')
            },
            success: function (response) {
                if (response.status === 'success') {
                    ids.forEach(function (id) {
                        $fileManagerModal.find('.fm-file-item[data-id="' + id + '"]').closest('.col').remove();
                    });
                    $deleteConfirmModal.modal('hide');
                    $bulkDeleteConfirmModal.modal('hide');
                    hideSelectionFooter();
                    checkEmptyState();
                } else {
                    var errorMessage = 'Delete failed: ' + (response.message || 'Unknown error');
                    showErrorAlert(errorMessage);
                }
            },
            error: function (xhr) {
                var errorMessage = 'Delete failed: ' + (xhr.responseJSON ? xhr.responseJSON.message : xhr.responseText);
                showErrorAlert(errorMessage);
            }
        });
    }

    /**
     * Initializes the Dropzone instance
     */
    function initializeDropzone() {
        Dropzone.autoDiscover = false;
        const dropzoneTarget = $fileManagerModal.find('.fm-dropzone-placeholder')[0];
        if (!dropzoneTarget) {
            console.error('Dropzone target .fm-dropzone-placeholder not found!');
            return;
        }

        // Read dynamic settings from modal data
        const allowedExtensions = $fileManagerModal.data('allowedExtensions');
        const dataSource = $fileManagerModal.data('currentDataSource');
        let maxFilesize = 10;

        if (dataSource == 'image' || dataSource == 'quiz_image' || dataSource == 'recipe_image' || dataSource == 'event_avatar_image') {
            maxFilesize = fileManagerConfig.maxFileUploadSize.image;
        } else if (dataSource == 'video') {
            maxFilesize = fileManagerConfig.maxFileUploadSize.video;
        } else if (dataSource == 'audio') {
            maxFilesize = fileManagerConfig.maxFileUploadSize.audio;
        } else if (dataSource == 'file') {
            maxFilesize = fileManagerConfig.maxFileUploadSize.file;
        }

        let dropzoneConfig = {
            url: fileManagerBaseUrl + 'upload',
            paramName: "file",
            maxFiles: 30,
            maxFilesize: parseInt(maxFilesize), // MB
            addRemoveLinks: true,
            dictFileTooBig: fileManagerConfig.text.uploadFileSizeError + " {{maxFilesize}}MB.",
            dictInvalidFileType: fileManagerConfig.text.invalidFileType,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="X-CSRF-TOKEN"]').attr('content')
            },
            init: function () {
                this.on("sending", function (file, xhr, formData) {
                    // Append the data source (image, file, audio) to the upload
                    formData.append('source', dataSource);
                });

                this.on("success", function (file, response) {
                    if (response.status === 'success' && response.file) {
                        prependFilesToDOM([response.file]);
                        checkEmptyState();
                        // Auto-remove successful uploads after 2 seconds
                        const _this = this;
                        setTimeout(function () {
                            _this.removeFile(file);
                        }, 2000);

                    } else {
                        console.error('Upload success, but server returned error:', response.message);
                        file.previewElement.classList.add("dz-error");

                        // Changed 'Server Error' fallback to your dynamic msgError
                        $(file.previewElement).find('.dz-error-message').text(response.message || fileManagerConfig.text.msgError);
                    }
                });

                this.on("error", function (file, errorMessage, xhr) {
                    console.error('Dropzone raw error:', errorMessage);

                    // Set the default fallback error message from your config
                    let displayMessage = fileManagerConfig.text.msgError;

                    // Check if errorMessage is a plain string (e.g., Dropzone's internal text)
                    if (typeof errorMessage === 'string') {
                        displayMessage = errorMessage;
                    }
                    // Check if it's an object (e.g., JSON response from server) preventing [object Object]
                    else if (typeof errorMessage === 'object' && errorMessage !== null) {
                        if (errorMessage.message) {
                            displayMessage = errorMessage.message;
                        } else if (errorMessage.error) {
                            displayMessage = errorMessage.error;
                        }
                    }

                    // Forcefully update the DOM element to show the clean message
                    const errorElement = file.previewElement.querySelector('.dz-error-message');
                    if (errorElement) {
                        errorElement.textContent = displayMessage;
                    }
                });
            }
        };

        // Format and set 'acceptedFiles' for Dropzone
        if (allowedExtensions) {
            // Convert "jpg,png" to ".jpg,.png"
            const extensionsArray = allowedExtensions.split(',');
            const formattedExtensions = extensionsArray.map(function (ext) {
                return '.' + ext.trim();
            }).join(',');

            dropzoneConfig.acceptedFiles = formattedExtensions;
        }

        myDropzoneInstance = new Dropzone(dropzoneTarget, dropzoneConfig);
    }

    /**
     * Prepends newly uploaded files to the grid
     */
    function prependFilesToDOM(files) {
        const gridContainer = $fileManagerModal.find('.fm-grid-view .row');
        files.forEach(file => {
            const gridEl = $(gridHtmlTemplate(file));
            gridEl.addClass('dynamically-loaded');
            gridContainer.prepend(gridEl);
        });
    }

    /**
     * Resets pagination and clears the grid to load files from scratch
     */
    function resetAndLoadFiles() {
        if (isLoading) {
            return;
        }
        currentPage = 1;
        hasMoreFiles = true;

        $fileManagerModal.find('.fm-grid-view .row').empty();
        modalBody.scrollTop(0);

        gridView.hide();
        emptyState.css('display', 'none');

        loadMoreFiles();
    }

    /**
     * Loads the next page of files via AJAX
     */
    function loadMoreFiles() {
        if (isLoading || !hasMoreFiles) {
            return;
        }
        isLoading = true;
        addLoadingIndicator();
        const dataSource = $fileManagerModal.data('currentDataSource') || 'image';
        const minLoadTime = 300; // Minimum load time for better UX

        $.ajax({
            url: fileManagerBaseUrl + 'list',
            type: 'GET',
            data: {
                page: currentPage,
                search: currentSearch,
                source: dataSource
            },
            dataType: 'json',
            success: function (response) {
                setTimeout(function () {
                    if (response && response.files) {
                        if (response.files.length > 0) {
                            appendFilesToDOM(response.files);
                            currentPage++;
                        }
                        hasMoreFiles = response.hasMore;
                    }
                    isLoading = false;
                    removeLoadingIndicator();
                    checkEmptyState();

                }, minLoadTime);
            },
            error: function (xhr) {
                console.error('AJAX Error:', xhr.responseText);
                hasMoreFiles = false;
                isLoading = false;
                removeLoadingIndicator();
                checkEmptyState();
            }
        });
    }

    function addLoadingIndicator() {
        $fileManagerModal.find('.fm-loading-indicator').show();
    }

    function removeLoadingIndicator() {
        $fileManagerModal.find('.fm-loading-indicator').hide();
    }

    function appendFilesToDOM(files) {
        const gridContainer = $fileManagerModal.find('.fm-grid-view .row');
        files.forEach(file => {
            const gridEl = $(gridHtmlTemplate(file));
            gridEl.addClass('dynamically-loaded');
            gridContainer.append(gridEl);
        });
    }

    /**
     * Generates the HTML for a single file item in the grid
     */
    function gridHtmlTemplate(file) {
        const txt = fileManagerConfig.text;
        const viewMode = $fileManagerModal.data('currentViewMode') || 'image';
        let thumbnailHtml = '';

        const fileId = file.id;
        const fileName = file.title;
        const fileAlt = file.alt_text || '';
        const fileUrl = file.file_url;
        const fileOrjUrl = file.file_orj_url || fileUrl;

        // Get 'is_downloadable' status
        const isDownloadable = file.is_downloadable == 1 ? 1 : 0;

        if (viewMode === 'image' && fileUrl) {
            thumbnailHtml = `<img src="${fileUrl}"
                            alt="${fileAlt}"
                            class="fm-file-thumbnail-grid">`;
        } else {
            // Get file extension text
            const iconClass = getFileIconClass(file.file_name);
            const extension = getFileExtension(file.file_name);
            const extensionHtml = extension
                ? `<span class="fm-file-extension">${extension}</span>`
                : '';

            thumbnailHtml = `<div class="fm-file-icon-wrapper">
                               <i class="bi ${iconClass}"></i>
                               ${extensionHtml}
                           </div>`;
        }

        return `
         <div class="col">
             <div class="fm-file-item"
                  data-id="${fileId}"
                  data-name="${fileName}"
                  data-url="${fileUrl}"
                  data-url-original="${fileOrjUrl}" 
                  data-alt="${fileAlt}"
                  data-is-downloadable="${isDownloadable}">
                       <div class="fm-custom-actions">
                         <button class="btn btn-sm btn-light btn-icon rounded-circle fm-action-toggle" type="button">
                             <i class="bi bi-three-dots-vertical"></i>
                         </button>
                         <ul class="fm-action-menu shadow-sm border-0">
                            <li>
                                <a class="dropdown-item fm-btn-edit-item" href="javascript:void(0)">
                                    <i class="ki-duotone ki-pencil me-2"><span class="path1"></span><span class="path2"></span></i>${txt.btnEdit}
                                </a>
                            </li>
                             <li>
                                <a class="dropdown-item fm-btn-delete-item-individual d-flex align-items-center" href="javascript:void(0)">
                                    <i class="ki-duotone ki-trash me-2"><span class="path1"></span><span classs="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>${txt.btnDelete}
                                </a>
                            </li>
                         </ul>
                     </div>
                 <div class="fm-file-item-thumbnail">
                     <i class="fm-check-icon bi bi-check-circle-fill"></i>
                     ${thumbnailHtml}
                 </div>
                 <div class="fm-file-item-info">
                     <span class="fm-file-name" title="${fileName}">${fileName}</span>
                 </div>
             </div>
         </div>`;
    }

    /**
     * Returns a Bootstrap icon class based on the file extension.
     */
    function getFileIconClass(fileName) {
        if (!fileName || typeof fileName !== 'string') {
            return 'bi-file-earmark';
        }

        const parts = fileName.split('.');
        if (parts.length === 1) {
            return 'bi-file-earmark';
        }

        const extension = parts.pop().toLowerCase();
        switch (extension) {
            case 'pdf':
                return 'bi-file-earmark-pdf';
            case 'doc':
            case 'docx':
                return 'bi-file-earmark-word';
            case 'xls':
            case 'xlsx':
                return 'bi-file-earmark-excel';
            case 'ppt':
            case 'pptx':
                return 'bi-file-earmark-ppt';
            case 'zip':
            case 'rar':
            case '7z':
            case 'gz':
                return 'bi-file-earmark-zip';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'webp':
            case 'bmp':
                return 'bi-file-earmark-image';
            case 'mp4':
            case 'mov':
            case 'avi':
            case 'mkv':
                return 'bi-file-earmark-play';
            case 'mp3':
            case 'wav':
            case 'ogg':
                return 'bi-file-earmark-music';
            case 'txt':
                return 'bi-file-earmark-text';
            case 'js':
            case 'json':
            case 'html':
            case 'css':
                return 'bi-file-earmark-code';
            default:
                return 'bi-file-earmark';
        }
    }

    // Core UI Helper Functions
    function showSelectionFooter() {
        selectionFooter.addClass('selection-active');
        const count = $fileManagerModal.find('.fm-file-item.active').length;
        const prefix = fileManagerConfig.text.selectionPrefix;
        const newText = prefix + count;
        selectionFooter.find('.fm-selection-count').text(newText);
    }

    function hideSelectionFooter() {
        selectionFooter.removeClass('selection-active');
        $fileManagerModal.find('.fm-file-item').removeClass('active');
    }

    function checkEmptyState() {
        if (!isLoading && $fileManagerModal.find('.fm-file-item').length === 0) {
            gridView.hide();
            emptyState.css('display', 'flex');
        } else {
            emptyState.css('display', 'none');
            gridView.show();
        }
    }

    /**
     * Helper: Adjusts UI for Page Mode (Card View)
     */
    function setupPageModeUI() {
        // Hide modal title, show tabs
        modalLabel.hide();
        const $tabs = $fileManagerModal.find('#fm-modal-tabs');
        $tabs.removeClass('d-none').css('display', 'flex');

        // Hide 'Select' button (it's meaningless in page mode)
        $fileManagerModal.find('.fm-btn-select-item').hide();

        // Tab click events are handled by server-side PHP routing.
    }

    /**
     * Helper: Gets file extension from filename
     */
    function getFileExtension(fileName) {
        if (fileName === null || typeof fileName === 'undefined') {
            return '';
        }
        const parts = fileName.split('.');
        if (parts.length === 1 || (parts[0] === '' && parts.length === 2)) {
            return '';
        }
        return parts.pop().toLowerCase();
    }

    /**
     * Helper: Show error with alert
     */
    function showErrorAlert(errorMessage) {
        Swal.fire({
            text: errorMessage,
            icon: 'error',
            confirmButtonText: fileManagerConfig.text.ok,
            buttonsStyling: false,
            showCancelButton: false,
            confirmButtonText: VrConfig.text.ok,
            customClass: {confirmButton: "btn btn-primary"}
        });
    }

    /**
     * Toggles the ... actions menu on a file item.
     */
    $fileManagerModal.on('click', '.fm-action-toggle', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $actionsDiv = $(this).closest('.fm-custom-actions');
        var wasActive = $actionsDiv.hasClass('active');
        $fileManagerModal.find('.fm-custom-actions.active').removeClass('active');
        if (!wasActive) {
            $actionsDiv.addClass('active');
        }
    });

    /**
     * Closes the ... actions menu if clicked outside.
     */
    $fileManagerModal.on('click', function (e) {
        if (!$(e.target).closest('.fm-action-toggle').length && !$(e.target).closest('.fm-action-menu').length) {
            $fileManagerModal.find('.fm-custom-actions.active').removeClass('active');
        }
    });

    /**
     * Handles selecting/deselecting a file item.
     */
    $fileManagerModal.on('click', '.fm-file-item', function (e) {
        // Prevent click if clicking on the actions menu
        if ($(e.target).closest('.fm-custom-actions').length) {
            return;
        }
        const $this = $(this);
        if (isMultiSelectEnabled) {
            $this.toggleClass('active');
        } else {
            const wasActive = $this.hasClass('active');
            $fileManagerModal.find('.fm-file-item').removeClass('active');
            if (!wasActive) {
                $this.addClass('active');
            }
        }
        // Show/hide the footer based on selection
        if ($fileManagerModal.find('.fm-file-item.active').length > 0) {
            showSelectionFooter();
        } else {
            hideSelectionFooter();
        }
    });

    // Checks if the file manager should run in "Page Mode"
    if (typeof mediaConfig !== 'undefined' && mediaConfig.isPageMode === true) {

        const selectMode = mediaConfig.selectMode || 'multi';
        const viewMode = mediaConfig.viewMode || 'image';
        const dataSource = mediaConfig.dataSource || 'image';
        const allowedExtensions = mediaConfig.allowedExtensions || null;

        $fileManagerModal.data('targetInput', null);
        $fileManagerModal.data('targetPreview', null);
        $fileManagerModal.data('targetPreviewContainer', null);
        $fileManagerModal.data('targetCsvInput', null);
        $fileManagerModal.data('targetListContainer', null);
        $fileManagerModal.data('currentViewMode', viewMode);
        $fileManagerModal.data('currentDataSource', dataSource);
        $fileManagerModal.data('allowedExtensions', allowedExtensions);

        // Set Dropzone extension badges
        const $extensionsDiv = $fileManagerModal.find('.fm-dropzone-extensions');
        $extensionsDiv.empty();
        if (allowedExtensions) {
            const extensionsArray = allowedExtensions.split(',');
            extensionsArray.forEach(function (ext) {
                const trimmedExt = ext.trim();
                if (trimmedExt.length > 0) {
                    const $badge = $('<span></span>')
                        .addClass('badge bg-secondary text-gray-600 me-1')
                        .text(trimmedExt.toUpperCase());
                    $extensionsDiv.append($badge);
                }
            });
        } else {
            $extensionsDiv.hide();
        }

        // Set global state
        isMultiSelectEnabled = (selectMode === 'multi');

        // Adjust UI for Page Mode
        setupPageModeUI();

        // Load files immediately
        resetAndLoadFiles();
    }

});