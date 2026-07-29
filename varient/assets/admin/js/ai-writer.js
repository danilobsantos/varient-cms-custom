(function ($) {
    "use strict";

    // ============================================================
    // DOM ELEMENTS & STATE MANAGEMENT
    // ============================================================
    const aiDOM = {
        // Modal & Layout
        modal: $('#modalAiWriter'),
        stepInput: $('#aiStepInput'),
        stepResult: $('#aiStepResult'),

        // AI Configuration Inputs (Inside Modal)
        inputTopic: $('textarea[name="ai_topic"]'),
        inputLang: $('input[name="ai_language"]'),
        selectType: $('select[name="ai_generate_type"]'),
        selectTone: $('select[name="ai_tone"]'),
        selectLength: $('select[name="ai_length"]'),

        // AI Generated Outputs (Inside Modal)
        outTitle: $('#aiResultTitle'),
        outSummary: $('#aiResultSummary'),
        outKeywords: $('#aiResultKeywords'),
        outContentDisplay: $('#aiResultContent'),
        outContentRaw: $('#aiResultContentRaw'),

        // Action Buttons
        btnGenerate: $('#btnAiGenerate'),
        btnRegenerate: $('#btnAiRegenerate'),
        btnReset: $('#btnAiReset'),
        btnUse: $('#btnAiUse'),
        groupResultActions: $('#aiResultButtons'),

        // STATE: Tracks which button triggered the modal
        activeTrigger: null
    };

    // ============================================================
    // APPLICATION LOGIC
    // ============================================================
    const AiWriterApp = {
        init: function () {
            this.bindEvents();
        },

        bindEvents: function () {

            // Capture Trigger Element on Modal Show
            $(document).on('click', '.btn-ai-writer-trigger', function() {
                aiDOM.activeTrigger = $(this);
            });

            // Capture Trigger Element on Modal Show
            // This is crucial for determining which list item to update.
            aiDOM.modal.on('show.bs.modal', function (event) {
                aiDOM.activeTrigger = $(event.relatedTarget);
            });

            // Clear Trigger on Modal Hide
            aiDOM.modal.on('hidden.bs.modal', function () {
                aiDOM.activeTrigger = null;
                AiWriterApp.resetInterface();
            });

            // Generate & Regenerate Actions
            aiDOM.btnGenerate.add(aiDOM.btnRegenerate).on('click', (e) => {
                e.preventDefault();
                const $btn = $(e.currentTarget);
                const isRegenerate = ($btn.attr('id') === 'btnAiRegenerate');
                this.handleGeneration($btn, isRegenerate);
            });

            // Transfer Content Action
            aiDOM.btnUse.on('click', (e) => {
                e.preventDefault();
                this.transferContentToMainForm();
            });

            // Reset Interface Action
            aiDOM.btnReset.on('click', (e) => {
                e.preventDefault();
                this.resetInterface();
            });
        },

        // ========================================================
        // CORE: API GENERATION HANDLER
        // ========================================================
        handleGeneration: function ($btn, isRegen) {
            const topicValue = aiDOM.inputTopic.val().trim();

            // Validation
            if (topicValue === '') {
                toastr.error(AiWriterConfig.translations.errorTopic);
                return;
            }

            // UI: Set Loading State
            $btn.attr("data-kt-indicator", "on").prop("disabled", true);

            if (isRegen) {
                this.toggleResultOpacity(true);
                aiDOM.btnReset.prop('disabled', true);
                aiDOM.btnUse.prop('disabled', true);
            }

            // Prepare Payload
            const requestData = {
                'topic': topicValue,
                'lang_name': aiDOM.inputLang.val(),
                'generate_type': aiDOM.selectType.val(),
                'tone': aiDOM.selectTone.val(),
                'length': aiDOM.selectLength.val()
            };

            // Fallback for generate_type
            if (!requestData.generate_type) requestData.generate_type = 'content_only';

            // Execute AJAX
            $.ajax({
                type: "POST",
                url: AiWriterConfig.endpoints.generate,
                data: requestData,
                dataType: "json",
                success: (response) => {
                    if (response.status === 'success') {

                        // Safety Check: Empty Content
                        if (!response.content || response.content.trim() === '') {
                            toastr.error(AiWriterConfig.translations.errorGenerate);
                            if (isRegen) this.toggleResultOpacity(false);
                            return;
                        }

                        this.populateResults(response);
                        this.transitionToResultView(isRegen);
                        toastr.success(AiWriterConfig.translations.successMsg);

                    } else {
                        // Backend Error
                        toastr.error(response.message || AiWriterConfig.translations.errorGeneric);
                        if (isRegen) this.toggleResultOpacity(false);
                    }
                },
                error: (xhr, status, error) => {
                    // Network/Server Error
                    toastr.error(AiWriterConfig.translations.errorGeneric + error);
                    if (isRegen) this.toggleResultOpacity(false);
                },
                complete: () => {
                    // Reset Loading State
                    $btn.removeAttr("data-kt-indicator").prop("disabled", false);
                    aiDOM.btnReset.prop('disabled', false);
                    aiDOM.btnUse.prop('disabled', false);
                }
            });
        },

        // ========================================================
        // HELPER METHODS: UI & DATA
        // ========================================================
        populateResults: function (data) {
            // Title
            aiDOM.outTitle.val(data.title || '');
            aiDOM.outTitle.closest('.div-result').toggle(!!data.title && data.title !== '');

            // Summary
            aiDOM.outSummary.val(data.summary || '');
            aiDOM.outSummary.closest('.div-result').toggle(!!data.summary && data.summary !== '');

            // Keywords
            aiDOM.outKeywords.val(data.keywords || '');
            aiDOM.outKeywords.closest('.div-result').toggle(!!data.keywords && data.keywords !== '');

            // Content
            const hasContent = (data.content && data.content.trim() !== '');
            aiDOM.outContentDisplay.html(data.content || '');
            aiDOM.outContentRaw.val(data.content || '');

            aiDOM.outContentDisplay.closest('.div-result').toggle(hasContent);
        },

        transitionToResultView: function (isRegen) {
            if (!isRegen) {
                // Initial Transition: Hide Input -> Show Result
                aiDOM.stepInput.addClass('d-none');
                aiDOM.stepResult.removeClass('d-none');
                aiDOM.btnGenerate.addClass('d-none');
                aiDOM.groupResultActions.removeClass('d-none');
            } else {
                // Regenerate Transition: Just restore opacity
                this.toggleResultOpacity(false);
            }
        },

        toggleResultOpacity: function (dim) {
            aiDOM.stepResult.css('opacity', dim ? '0.5' : '1');
        },

        resetInterface: function () {
            // Reset Views
            aiDOM.stepResult.addClass('d-none');
            aiDOM.stepInput.removeClass('d-none');
            aiDOM.groupResultActions.addClass('d-none');
            aiDOM.btnGenerate.removeClass('d-none');

            // Clear Data
            aiDOM.outTitle.val('');
            aiDOM.outSummary.val('');
            aiDOM.outKeywords.val('');
            aiDOM.outContentDisplay.html('');
            aiDOM.outContentRaw.val('');
        },

        // ========================================================
        // CONTENT TRANSFER LOGIC (CONTEXT AWARE)
        // ========================================================

        /**
         * Locates the target input field based on the trigger button's context.
         * * Strategy:
         * 1. If a trigger button exists, search its closest containers (Card Body, Collapse, Row).
         * 2. If found within context, return that specific input.
         * 3. If not found (or no trigger), fallback to global document search.
         * * @param {string} selector - The CSS class of the input to find.
         * @returns {jQuery} - The found jQuery element.
         */

        findTargetElement: function(selector) {
            if (aiDOM.activeTrigger && aiDOM.activeTrigger.length > 0) {
                // Locate the nearest structural container (card, collapse, or row)
                // to establish a search scope relative to the clicked button.
                const $container = aiDOM.activeTrigger.closest('.card-body, .collapse, .card, .fv-row');

                if ($container.length > 0) {
                    const $scopedElement = $container.find(selector);
                    if ($scopedElement.length > 0) {
                        // Return only the FIRST match within this specific container
                        // to prevent accidental data leakage to other list items.
                        return $scopedElement.first();
                    }
                }
            }
            // Fallback: If no specific context is found, target the first occurrence in the DOM
            // to avoid a mass-update bug across all matching selectors.
            return $(selector).first();
        },

        transferContentToMainForm: function () {
            const generatedTitle = aiDOM.outTitle.val();
            const generatedSummary = aiDOM.outSummary.val();
            const generatedKeys = aiDOM.outKeywords.val();

            // Locate elements relative to the clicked button
            const $targetTitle = this.findTargetElement(AiWriterConfig.selectors.targetTitle);
            const $targetSummary = this.findTargetElement(AiWriterConfig.selectors.targetSummary);
            const $targetKeywords = this.findTargetElement(AiWriterConfig.selectors.targetKeywords);

            // Transfer Title
            if (generatedTitle && $targetTitle.length) {
                $targetTitle.val(generatedTitle).trigger('change');
                $targetTitle.val(generatedTitle).trigger('input');
            }

            // Transfer Summary
            if (generatedSummary && $targetSummary.length) {
                $targetSummary.val(generatedSummary);
            }

            // Transfer Keywords (with Tagify support)
            if (generatedKeys && $targetKeywords.length) {
                if ($targetKeywords.data('tagify')) {
                    try {
                        $targetKeywords.data('tagify').addTags(generatedKeys);
                    } catch (e) {
                        console.warn('Tagify error:', e);
                    }
                } else {
                    $targetKeywords.val(generatedKeys);
                }
            }

            // Transfer Rich Text Content
            const rawContent = aiDOM.outContentRaw.val();
            if (rawContent) {
                this.setEditorContent(rawContent);
            }

            // Close Modal
            aiDOM.modal.modal('hide');
        },

        setEditorContent: function (htmlContent) {
            const targetSelector = AiWriterConfig.selectors.targetContent;

            // Find the specific textarea for the active context
            let $targetEditor = this.findTargetElement(targetSelector);

            if ($targetEditor.length > 0) {
                const editorId = $targetEditor.attr('id');

                // TinyMCE Integration
                // TinyMCE instances are accessed by ID. We must find the ID of the scoped textarea.
                if (typeof tinymce !== 'undefined' && editorId) {
                    const editorInstance = tinymce.get(editorId);
                    if (editorInstance) {
                        editorInstance.setContent(htmlContent);
                        return;
                    }
                }

                // Summernote Integration
                if (typeof $.fn.summernote !== 'undefined') {
                    $targetEditor.summernote('code', htmlContent);
                    return;
                }

                // Fallback: Standard Textarea
                $targetEditor.val(htmlContent);

            } else {
                // Fallback: Global Active Editor (if scoped search failed)
                if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
                    tinymce.activeEditor.setContent(htmlContent);
                }
            }
        }
    };

    // Initialize Application
    $(document).ready(function () {
        AiWriterApp.init();
    });

})(jQuery);