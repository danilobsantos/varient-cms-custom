<div class="mt-3">
    <h2 class="mb-4 fw-bolder"><?= trans("questions"); ?></h2>

    <div id="questionsContainer">
        <?php if (!empty($quizQuestions)) {
            foreach ($quizQuestions as $question) {
                echo view('admin/post/quiz/_question', ['postFormat' => $postFormat, 'question' => $question]);
            }
        } else {
            echo view('admin/post/quiz/_question', ['postFormat' => $postFormat, 'numItems' => 0]);
        } ?>
    </div>

    <div class="d-flex justify-content-center mt-6 mb-10">
        <button type="button" class="btn btn-success btn-add-quiz-question" data-post-format="<?= esc($postFormat, 'attr'); ?>">
            <i class="ki-duotone ki-plus fs-2"></i>
            <?= trans("add_question"); ?>
        </button>
    </div>
</div>