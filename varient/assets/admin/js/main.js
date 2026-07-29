/**
 * Admin Panel Custom JavaScript
 * Encapsulated via IIFE to prevent global scope pollution.
 * Required functions are explicitly exposed to the window object for backward compatibility.
 */
(function ($, window, document) {
    'use strict';

    // ========================================================================
    // CONFIGURATION & AJAX SETUP
    // ========================================================================

    /* CSRF Protection Setup */
    $.ajaxSetup({
        beforeSend: function (xhr, settings) {
            const csrfHash = $('meta[name="X-CSRF-TOKEN"]').attr('content');
            if (settings.type.toUpperCase() === 'POST') {
                if (typeof settings.data === 'string') {
                    settings.data += '&' + VrConfig.csrfTokenName + '=' + csrfHash;
                    settings.data += '&sysLangId=' + VrConfig.sysLangId;
                } else if (typeof settings.data === 'object') {
                    settings.data = settings.data || {};
                    settings.data[VrConfig.csrfTokenName] = csrfHash;
                }
            }
        }
    });

    /* Toastr Global Options */
    toastr.options = {
        closeButton: true,
        debug: false,
        newestOnTop: false,
        progressBar: true,
        preventDuplicates: false,
        showDuration: "300",
        hideDuration: "1000",
        timeOut: "5000",
        extendedTimeOut: "1000",
        showEasing: "swing",
        hideEasing: "linear",
        showMethod: "fadeIn",
        hideMethod: "fadeOut",
        positionClass: VrConfig.isRtl ? 'toastr-top-left' : 'toastr-top-right'
    };

    // ========================================================================
    // GLOBAL UTILITY FUNCTIONS (Exposed to Window)
    // ========================================================================

    window.setSerializedData = function (serializedData) {
        serializedData.push({ name: 'sysLangId', value: VrConfig.sysLangId });
        serializedData.push({ name: VrConfig.csrfTokenName, value: $('meta[name="X-CSRF-TOKEN"]').attr('content') });
        return serializedData;
    };

    window.generateUrl = function (path) {
        return VrConfig.baseUrl + path;
    };

    window.swalOptions = function (message, type = 'warning', singleButton = false) {
        if (singleButton) {
            return {
                text: message,
                icon: type,
                buttonsStyling: false,
                showCancelButton: false,
                confirmButtonText: VrConfig.text.ok,
                customClass: { confirmButton: "btn btn-primary" }
            };
        }

        return {
            text: message,
            icon: type,
            buttonsStyling: false,
            showCancelButton: true,
            confirmButtonText: VrConfig.text.yes,
            cancelButtonText: VrConfig.text.cancel,
            reverseButtons: true,
            customClass: {
                confirmButton: "btn btn-primary",
                cancelButton: 'btn btn-secondary'
            }
        };
    };

    // Dropzone utilities
    window.appendDropzoneUploadedFile = function (file, response, dzContainer) {
        if (response.success && response.paths) {
            const paths = response.paths;
            const uuid = file.upload.uuid;

            const input = document.createElement("input");
            input.type = "hidden";
            input.name = "uploaded_files[]";
            input.value = JSON.stringify(paths);
            input.setAttribute("data-uuid", uuid);

            file._uuid = uuid;
            dzContainer.querySelector(".uploaded-files").appendChild(input);
        }
    };

    window.deleteDropzoneUploadedFile = function (file, dzContainer) {
        if (file._uuid) {
            const input = dzContainer.querySelector(`.uploaded-files input[data-uuid="${file._uuid}"]`);
            if (input) {
                const fileData = input.value;
                $.ajax({
                    url: window.generateUrl("Ajax/deleteDropzoneUploadedFile"),
                    type: "POST",
                    data: { file_data: fileData }
                });
                input.remove();
            }
        }
        if (file.previewElement) {
            file.previewElement.remove();
        }
    };

    // Category and Menu AJAX utilities
    window.getMenuLinksByLang = function (val) {
        $.ajax({
            type: 'POST',
            url: window.generateUrl('Admin/getMenuLinksByLang'),
            data: { "lang_id": val },
            dataType: 'json',
            success: function (response) {
                if (response.status && response.html) {
                    const $parentLinks = $('#parent_links');
                    $parentLinks.empty();
                    $parentLinks.append('<option value="0">' + VrConfig.text.none + '</option>');
                    $parentLinks.append(response.html);
                }
            }
        });
    };

    window.getAlbumsByLang = function (val) {
        if (!val) return;
        $.ajax({
            type: 'POST',
            url: window.generateUrl('Gallery/getAlbumsByLang'),
            data: { "lang_id": val },
            success: function (response) {
                if (response.result) {
                    const $albums = $('#albums');
                    $albums.empty().append($('<option>', { value: '', text: '' }));
                    $('#categories').empty();

                    if (response.albums && response.albums.length > 0) {
                        response.albums.forEach(item => {
                            $albums.append($('<option>', { value: item.id, text: item.name }));
                        });
                    }
                }
            }
        });
    };

    window.getGalleryCategoriesByAlbum = function (val) {
        if (!val) return;
        $.ajax({
            type: 'POST',
            url: window.generateUrl('Gallery/getCategoriesByAlbum'),
            data: { "album_id": val },
            success: function (response) {
                if (response.result) {
                    const $categories = $('#categories');
                    $categories.empty().append($('<option>', { value: '', text: '' }));
                    if (response.categories && response.categories.length > 0) {
                        response.categories.forEach(item => {
                            $categories.append($('<option>', { value: item.id, text: item.name }));
                        });
                    }
                }
            }
        });
    };

    window.getParentCategoriesByLang = function (langId, targetSelector, childSelectorToClear = null) {
        if (!langId || !targetSelector) return;

        const $targetElement = $(targetSelector);
        if (!$targetElement.length) return;

        if (childSelectorToClear) {
            const $childElement = $(childSelectorToClear);
            if ($childElement.length) $childElement.empty();
        }

        $.ajax({
            type: 'POST',
            url: window.generateUrl('Ajax/getParentCategoriesByLang'),
            data: { lang_id: langId },
            success: function (response) {
                if (response.result) {
                    $targetElement.empty().append($('<option>', { value: '', text: '' }));
                    if (response.categories && response.categories.length > 0) {
                        response.categories.forEach(item => {
                            $targetElement.append($('<option>', { value: item.id, text: item.name }));
                        });
                    }
                }
            }
        });
    };

    window.getSubCategories = function (parentId, targetSelector) {
        if (!parentId || !targetSelector) return;

        const $targetElement = $(targetSelector);
        if (!$targetElement.length) return;

        $.ajax({
            type: 'POST',
            url: window.generateUrl('Ajax/getSubCategories'),
            data: { parent_id: parentId },
            success: function (response) {
                if (response.result) {
                    $targetElement.empty().append($('<option>', { value: '', text: '' }));
                    if (response.categories && response.categories.length > 0) {
                        response.categories.forEach(item => {
                            $targetElement.append($('<option>', { value: item.id, text: item.name }));
                        });
                    }
                }
            }
        });
    };

    // TinyMCE utilities
    window.initTinyMCE = function (selector, minHeight, toolbar) {
        let menuBar = 'file edit view insert format tools table help';
        if (!toolbar) {
            toolbar = 'fullscreen code preview | undo redo | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | numlist bullist | forecolor backcolor removeformat | image media link emoticons';
        }
        if (selector === '.tinyMCEsmall') {
            menuBar = false;
            toolbar = 'fullscreen code preview | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | numlist bullist | forecolor backcolor removeformat | image media link emoticons';
        }

        const isDark = (KTThemeMode.getMode() === "dark");

        tinymce.init({
            selector: selector,
            height: minHeight,
            min_height: minHeight,
            valid_elements: '*[*]',
            entity_encoding: 'raw',
            relative_urls: false,
            remove_script_host: false,
            directionality: VrConfig.directionality,
            language: VrConfig.textEditorLang,
            menubar: menuBar,
            plugins: 'advlist autolink lists link image charmap preview searchreplace visualblocks code codesample fullscreen insertdatetime media table emoticons',
            toolbar: toolbar,
            contextmenu: 'link image table | copy paste cut | selectall',
            skin: isDark ? "oxide-dark" : "oxide",
            content_css: isDark ? [VrConfig.textEditorCssPath, "dark"] : [VrConfig.textEditorCssPath],
            mobile: { menubar: menuBar }
        });
    };

    window.initTinyEditors = function () {
        if ($('.tinyMCE').length > 0) window.initTinyMCE('.tinyMCE', 400, null);
        if ($('.tinyMCEsmall').length > 0) window.initTinyMCE('.tinyMCEsmall', 192, null);
    };


    // ========================================================================
    // DOM READY EVENT DELEGATIONS & INITIALIZATIONS
    // ========================================================================
    $(function () {

        // Append back_url to POST forms
        $('form[method="post" i]').append(function () {
            return $('<input>', { type: 'hidden', name: 'frm_back_url', value: VrConfig.currentUrl });
        });

        // Initialize Form loading indicators (Metronic)
        document.querySelectorAll(".kt-form").forEach(form => {
            form.addEventListener("submit", function (e) {
                e.stopPropagation();
                const submitter = e.submitter || this.querySelector('[type="submit"][data-kt-indicator]');
                if (!submitter) return;

                submitter.setAttribute("data-kt-indicator", "on");
                submitter.disabled = true;

                // Safety reset fallback
                setTimeout(() => {
                    submitter.removeAttribute("data-kt-indicator");
                    submitter.disabled = false;
                }, 10000);
            });
        });

        // Submit filter form with row number select
        $('.form-filter .select-filter-rows').on('change', function () {
            $(this).closest('form').submit();
        });

        // Load location options for widgets
        if ($('#formAddWidget').length) {
            const $categorySelect = $('select[name="display_category_id"]');
            const $langSelect = $('select[name="lang_id"]');
            let initialCategoryId = $categorySelect.data('selected-id');
            initialCategoryId = (initialCategoryId === null || initialCategoryId === undefined || initialCategoryId === '') ? '0' : initialCategoryId;

            const updateCategoriesByLang = (langId, selectedId = '0') => {
                if ($categorySelect.hasClass('select2-hidden-accessible')) {
                    $categorySelect.select2('destroy');
                }
                $categorySelect.empty();
                let isAnySelected = false;

                widgetCategories.forEach(cat => {
                    if (cat.lang_id === 'all' || cat.lang_id == langId) {
                        let targetId = (selectedId === null || selectedId === '') ? '0' : selectedId;
                        const isSelected = (cat.id == targetId);
                        if (isSelected) isAnySelected = true;
                        $categorySelect.append(new Option(cat.name, cat.id, isSelected, isSelected));
                    }
                });

                if (!isAnySelected) $categorySelect.val(null);

                $categorySelect.select2({
                    placeholder: $categorySelect.data('placeholder'),
                    minimumResultsForSearch: Infinity
                });
            };

            $langSelect.on('change', function () {
                updateCategoriesByLang($(this).val(), '0');
            });
            updateCategoriesByLang($langSelect.val(), initialCategoryId);
        }

        // Initialize Select2 dropdowns (Users & Categories)
        const initSelect2Ajax = ($element, urlEndpoint, textCallback) => {
            if (!$element.length) return;

            $element.select2({
                placeholder: VrConfig.textSelect,
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    type: 'POST',
                    url: window.generateUrl(urlEndpoint),
                    dataType: 'json',
                    data: params => ({ q: params.term }),
                    processResults: data => ({
                        results: data.items.map(item => ({ id: item.id, text: textCallback(item) }))
                    }),
                    cache: true
                },
                language: {
                    noResults: () => VrConfig.text.searchNoResult,
                    searching: () => VrConfig.text.searching,
                    inputTooShort: () => VrConfig.text.enter2Characters,
                    errorLoading: () => VrConfig.text.searchNoResult
                },
                dir: VrConfig.directionality,
                width: '100%'
            }).on('select2:open', function () {
                $('.select2-container--open .select2-search__field').attr('placeholder', VrConfig.text.search);
            });
        };

        initSelect2Ajax($('.select2-users'), "Admin/populateUsersDropdown", item => `${item.id}: ${item.username}`);
        initSelect2Ajax($('.select2-categories'), "Category/populateCategoriesDropdown", item => `${item.id}: ${item.name}`);

        // Custom File Upload Button Wrapper
        $('.file-upload').each(function () {
            const $wrapper = $(this);
            const $input = $wrapper.find('[data-upload-input]');
            const $button = $wrapper.find('[data-upload-button]');
            const $filename = $wrapper.find('[data-upload-filename]');

            $button.on('click', () => $input.trigger('click'));
            $input.on('change', function () {
                const file = this.files[0];
                $filename.text(file ? file.name : '');
            });
        });

        // Toggles visibility for order fields
        const handleSwitchToggle = (switchSelector, containerSelector) => {
            const $switch = $(switchSelector);
            const $container = $(containerSelector);
            if ($switch.is(':checked')) $container.show();
            $switch.on('change', function () { $container.toggle(this.checked); });
        };
        handleSwitchToggle('input[name="is_slider"]', '#sliderOrderInputContainer');
        handleSwitchToggle('input[name="is_featured"]', '#featuredOrderInputContainer');

        // Post Category Validation
        const postForm = document.getElementById('form-post');
        if (postForm) {
            postForm.addEventListener('submit', function (e) {
                const submitBtn = e.submitter;
                const catInput = document.querySelector('input[name="category_id"]');

                if (!catInput || catInput.value === '0' || catInput.value === '') {
                    e.preventDefault();
                    if (submitBtn) {
                        submitBtn.removeAttribute('data-kt-indicator');
                        submitBtn.disabled = false;
                    }
                    Swal.fire(window.swalOptions(VrConfig.text.msgSelectCategory, 'warning', true));
                }
            });
        }

        // Initialize TinyMCE Editors
        window.initTinyEditors();
    });


    // ========================================================================
    // DOCUMENT-LEVEL EVENT DELEGATIONS
    // ========================================================================

    /* Universal Action Handler (Single & Bulk) */
    $(document).on('click', '.js-action-trigger', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const url = $btn.data('url');
        const action = $btn.data('action');
        const message = $btn.data('message');
        const singleId = $btn.data('id');
        const targetSelector = $btn.data('target');
        const needsConfirm = $btn.data('confirm') == 1;

        let ajaxData = { action: action };
        let selectedIds = [];

        // Resolve IDs
        if (singleId) {
            ajaxData.id = singleId;
            selectedIds.push(singleId);
        } else {
            $('.table-bulk .form-check-input:checked').each(function () {
                const val = $(this).val();
                if (val && !$(this).data('kt-check')) selectedIds.push(val);
            });
        }

        if (selectedIds.length === 0) {
            Swal.fire({ text: "Please select at least one item.", icon: "warning" });
            return;
        }

        ajaxData.ids = selectedIds;

        const executeAction = () => {
            $btn.prop('disabled', true);
            $.ajax({
                type: 'POST',
                url: url,
                data: ajaxData,
                dataType: 'json',
                success: function (response) {
                    if (action === 'delete' && targetSelector) {
                        $(targetSelector).slideUp(300, function () { $(this).remove(); });
                    } else {
                        location.reload();
                    }
                },
                error: function (xhr) {
                    console.error("Action Error:", xhr.responseText);
                    Swal.fire({ text: "An error occurred during the process.", icon: "error" });
                },
                complete: function () {
                    $btn.prop('disabled', false);
                }
            });
        };

        if (needsConfirm && message) {
            Swal.fire(window.swalOptions(message)).then((result) => {
                if (result.isConfirmed) executeAction();
            });
        } else {
            executeAction();
        }
    });

    /* Bulk actions toolbar visibility */
    $(document).on('change', '.table-bulk .form-check-input', function () {
        const $checkedBoxes = $('.table-bulk tbody .form-check-input:checked');
        const $toolbar = $('#toolbarBulkActions');

        if ($checkedBoxes.length > 0) {
            $toolbar.removeClass('d-none');
            if ($toolbar.data('kt-check-pending')) {
                const targetButtonSelector = $toolbar.data('kt-check-pending-target');
                if (!targetButtonSelector) return;

                const hasPending = $checkedBoxes.filter(function () {
                    return $(this).data('status') != '1';
                }).length > 0;

                $(targetButtonSelector).toggleClass('d-none', !hasPending);
            }
        } else {
            $toolbar.addClass('d-none');
        }
    });

    /* Language translation live edit (Debounced) */
    $(document).on('input', '.input-language-translation', function () {
        const $input = $(this);
        const id = $input.data('id');
        const value = $input.val().trim();

        clearTimeout($input.data('timer'));
        if ($input.data('request')) $input.data('request').abort();

        if (value === '') {
            $input.css('background-color', '');
            return;
        }

        $input.data('timer', setTimeout(() => {
            const newRequest = $.ajax({
                url: window.generateUrl('Language/editTranslation'),
                type: 'POST',
                data: { id: id, translation: value },
                success: function (response) {
                    $input.css('background-color', response.status ? '#e8f5e9' : '#f8d7da');
                    setTimeout(() => $input.css('background-color', ''), 300);
                },
                error: function (jqXHR) {
                    if (jqXHR.statusText === 'abort') return;
                    $input.css('background-color', '#f8d7da');
                    setTimeout(() => $input.css('background-color', ''), 300);
                },
                complete: () => $input.data('request', null)
            });
            $input.data('request', newRequest);
        }, 500));
    });

    /* Dynamic subcategory loader */
    $(document).on('click', '.category-trigger', function (e) {
        e.stopPropagation();
        const $this = $(this);
        const id = $this.data('id');
        const isLoaded = $this.data('loaded');
        const $target = $(`#children-${id}`);
        const $arrow = $this.find('.arrow-icon');
        const $spinner = $this.find('.loading-spinner');

        if ($target.is(':visible')) {
            $arrow.removeClass('rotate-90');
            $target.slideUp(200);
        } else {
            $arrow.addClass('rotate-90');
            if (!isLoaded) {
                $spinner.show();
                const urlParams = new URLSearchParams(window.location.search);
                const langId = urlParams.get('lang_id') || '';
                $.get(`${VrConfig.adminUrl}/categories`, { parent_id: id, lang_id: langId }, function (html) {
                    $target.html(html).slideDown(200);
                    $this.data('loaded', true);
                    $spinner.hide();
                });
            } else {
                $target.slideDown(200);
            }
        }
    });

    /* Post list items management */
    $(document).on('input', '.item-title-input', function () {
        $(this).closest('.card').find('.item-title-label').text($(this).val());
    });

    $(document).on('click', '.btn-add-list-item', function () {
        const numItems = $('#listItemsContainer .card').length;
        $.ajax({
            type: 'POST',
            url: window.generateUrl('Post/addListItem'),
            data: { post_format: $(this).data('post-format'), num_items: numItems },
            success: response => {
                if (response.result) $('#listItemsContainer').append(response.htmlContent);
            }
        });
    });

    $(document).on('click', '.btn-remove-list-item', function () {
        const id = $(this).data('id');
        Swal.fire(window.swalOptions(VrConfig.text.confirmDelete)).then(result => {
            if (result.isConfirmed) $('#list_item_card_' + id).remove();
        });
    });

    /* Cancel email modal confirmation */
    $(document).on('click', '.btn-close-modal-confirm', function (e) {
        e.preventDefault();
        const subject = $('#email_subject').val().trim();
        let body = '';
        if (tinymce.activeEditor) {
            body = tinymce.activeEditor.getContent({ format: 'text' }).trim();
        }

        if (subject.length > 0 || body.length > 0) {
            Swal.fire({
                text: VrConfig.text.msgCancelEmailSending,
                icon: "warning",
                buttonsStyling: false,
                showCancelButton: true,
                confirmButtonText: VrConfig.text.yes,
                cancelButtonText: VrConfig.text.no,
                reverseButtons: true,
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: 'btn btn-secondary'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof emailQueue !== 'undefined' && emailQueue !== null) {
                        emailQueue.isSending = false;
                    }
                    $('#modalSendEmail').modal('hide');
                }
            });
        } else {
            $('#modalSendEmail').modal('hide');
        }
    });

    // ========================================================================
    // EXTERNAL BINDINGS
    // ========================================================================

    // Re-init editors on AJAX complete
    $(document).ajaxComplete(window.initTinyEditors);

    // KTThemeMode theme change listener
    if (typeof KTThemeMode !== 'undefined') {
        KTThemeMode.on("kt.thememode.change", function () {
            tinymce.remove();
            window.initTinyEditors();
        });
    }

})(jQuery, window, document);