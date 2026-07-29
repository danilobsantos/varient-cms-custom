<?php
$formats = [
    'small_image' => 'custom-grid-container grid-3-cols',
    'large_image' => 'custom-grid-container grid-2-cols',
    'text' => 'row'
];
$format = $question->answer_format ?? 'small_image';
$containerClass = $formats[$format] ?? '';
?>

<div class="quiz-answers-container">
    <h3 class="fw-bolder mt-6"><?= trans("answers"); ?></h3>

    <div class="format-toolbar">
        <span class="text-gray-800 mb-2"><?= trans("answer_format"); ?></span>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-light btn-quiz-answer-layout <?= $format === 'small_image' ? 'active' : ''; ?>" data-question-id="<?= esc($questionId, 'attr'); ?>" data-format="small_image">
                <i class="bi bi-grid-3x3-gap-fill fs-6 text-gray-600"></i>
            </button>
            <button type="button" class="btn btn-light btn-quiz-answer-layout <?= $format === 'large_image' ? 'active' : ''; ?>" data-question-id="<?= esc($questionId, 'attr'); ?>" data-format="large_image">
                <i class="bi bi-grid-fill fs-6 text-gray-600"></i>
            </button>
            <button type="button" class="btn btn-light btn-quiz-answer-layout <?= $format === 'text' ? 'active' : ''; ?>" data-question-id="<?= esc($questionId, 'attr'); ?>" data-format="text">
                <i class="bi bi-list fs-2 text-gray-600 p-0"></i>
            </button>
        </div>

        <input type="hidden" id="input_answer_format_<?= $questionId ?>" name="questions[<?= $questionId ?>][answer_format]" value="<?= esc($format); ?>">
    </div>

    <div id="quizAnswersContainer<?= $questionId ?>" class="<?= $containerClass; ?>">
        <?php if (!empty($quizAnswers) && !empty($quizAnswers[$questionId])) {
            foreach ($quizAnswers[$questionId] as $answer) {
                echo view('admin/post/quiz/_answer', ['postFormat' => $postFormat, 'questionId' => $questionId, 'answerFormat' => $question->answer_format, 'answer' => $answer]);
            }
        } else {
            echo view('admin/post/quiz/_answer', ['postFormat' => $postFormat, 'questionId' => $questionId, 'answerFormat' => $questionId]);
        } ?>
    </div>

    <div class="d-flex justify-content-center mt-6">
        <button type="button" class="btn btn-light-success btn-add-quiz-answer"
                data-post-format="<?= esc($postFormat, 'attr'); ?>"
                data-question-id="<?= esc($questionId, 'attr'); ?>"
                data-answer-format="<?= esc($format, 'attr'); ?>">
            <i class="bi bi-plus-lg"></i>&nbsp;<?= trans("add_answer"); ?>
        </button>
    </div>
</div>