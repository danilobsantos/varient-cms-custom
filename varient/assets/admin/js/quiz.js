// Add new quiz question
$(document).on('click', '.btn-add-quiz-question', function () {
    const numItems = $('#questionsContainer .card').length;
    const postFormat = $(this).data('post-format');

    $.ajax({
        type: 'POST',
        url: generateUrl('Post/addQuizItem'),
        data: {
            'item_type': 'question',
            'post_format': postFormat,
            'num_items': numItems
        },
        success: function (response) {
            if (response.result) {
                $('#questionsContainer').append(response.htmlContent);

                populateQuizResultSelects();
            }
        }
    });
});

// Add new quiz answer
$(document).on('click', '.btn-add-quiz-answer', function () {
    const postFormat = $(this).data('post-format');
    const questionId = $(this).data('question-id');
    const answerFormat = $('#input_answer_format_' + questionId).val();
    $.ajax({
        type: 'POST',
        url: generateUrl('Post/addQuizItem'),
        data: {
            'item_type': 'answer',
            'post_format': postFormat,
            'question_id': questionId,
            'answer_format': answerFormat
        },
        success: function (response) {
            if (response.result) {
                $('#quizAnswersContainer' + questionId).append(response.htmlContent);

                populateQuizResultSelects();
            }
        }
    });
});

// Add new quiz result
$(document).on('click', '.btn-add-quiz-result', function () {
    const numItems = $('#resultsContainer .card').length;
    const postFormat = $(this).data('post-format');

    $.ajax({
        type: 'POST',
        url: generateUrl('Post/addQuizItem'),
        data: {
            'item_type': 'result',
            'post_format': postFormat,
            'num_items': numItems
        },
        success: function (response) {
            if (response.result) {
                $('#resultsContainer').append(response.htmlContent);
            }
        }
    });
});

// Populates select elements with quiz result options while preserving current selection
function populateQuizResultSelects(selector = '.quiz-result-select') {
    if (typeof quizResults !== 'undefined' && Array.isArray(quizResults)) {
        $(selector).each(function () {
            const $targetElement = $(this);

            let currentSelection = $targetElement.val();

            if (!currentSelection && currentSelection !== 0) {
                currentSelection = $targetElement.data('selected-id');
            }

            $targetElement.empty();
            $targetElement.append($('<option>', {value: '', text: VrConfig.text.selectResult}));

            if (quizResults && quizResults.length > 0) {
                quizResults.forEach(function (item) {
                    const option = $('<option>', {
                        value: item.id,
                        text: item.title
                    });
                    $targetElement.append(option);
                });
            }

            if (currentSelection !== undefined && currentSelection !== null && currentSelection !== '') {
                $targetElement.val(currentSelection);
            }
        });
    }
}

$(document).ready(function () {
    // Updates the quizResults array whenever an input
    $(document).on('input', '.input-quiz-result-title', function () {
        if (typeof quizResults !== 'undefined' && Array.isArray(quizResults)) {
            const id = $(this).data('result-id');
            const text = $(this).val();

            if (!id) return;

            const index = quizResults.findIndex(item => item.id == id);
            if (index !== -1) {
                quizResults[index].title = text;
            } else {
                quizResults.push({id: id, title: text});
            }

            populateQuizResultSelects();
        }
    });

    populateQuizResultSelects();

    // Switch Layout
    $(document).on('click', '.btn-quiz-answer-layout', function () {
        const $btn = $(this);
        const questionId = $btn.data('question-id');
        const format = $btn.data('format');
        const $container = $('#quizAnswersContainer' + questionId);
        const $wrapper = $container.find('.answer-wrapper');
        const $card = $container.find('.answer-card');

        // toggle active class
        $('.btn-quiz-answer-layout').removeClass('active');
        $btn.addClass('active');

        // update hidden input
        $('#input_answer_format_' + questionId).val(format);

        // reset classes
        $wrapper.removeClass('answer-wrapper-text col-12 mb-3');
        $card.removeClass('answer-card-fixed');
        $container.removeClass('row custom-grid-container grid-3-cols grid-2-cols');

        // apply new layout classes
        switch (format) {
            case 'small_image':
                $container.addClass('custom-grid-container grid-3-cols');
                $card.addClass('answer-card-fixed');
                break;
            case 'large_image':
                $container.addClass('custom-grid-container grid-2-cols');
                $card.addClass('answer-card-fixed');
                break;
            case 'text':
                $container.addClass('row');
                $wrapper.addClass('answer-wrapper-text col-12 mb-3');
                break;
        }
    });

    // Delete a question
    $(document).on('click', '.btn-delete-quiz-question', function () {
        Swal.fire(swalOptions(VrConfig.text.confirmDelete)).then((result) => {
            if (result.isConfirmed) {
                const id = $(this).data('id');
                $('#question_card_' + id).remove();
            }
        });
    });

    // Delete an answer
    $(document).on('click', '.btn-delete-quiz-answer', function () {
        Swal.fire(swalOptions(VrConfig.text.confirmDelete)).then((result) => {
            if (result.isConfirmed) {
                const id = $(this).data('id');
                $('#answer_card_' + id).remove();
            }
        });
    });

    // Delete a result
    $(document).on('click', '.btn-delete-quiz-result', function () {
        Swal.fire(swalOptions(VrConfig.text.confirmDelete)).then((result) => {
            if (result.isConfirmed) {
                const id = $(this).data('id');
                $('#result_card_' + id).remove();

                if (typeof quizResults !== 'undefined' && Array.isArray(quizResults)) {
                    const index = quizResults.findIndex(item => item.id == id);
                    if (index !== -1) {
                        quizResults.splice(index, 1);
                    }

                    populateQuizResultSelects();
                }
            }
        });
    });
});