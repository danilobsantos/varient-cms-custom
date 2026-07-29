<?php
$jsResultArray = [];

if (!empty($quizResults)) {
    $jsResultArray = array_map(function ($item) {
        return [
                'id'    => $item->id,
                'order' => $item->result_order,
                'title' => $item->result_title
        ];
    }, $quizResults);
}
?>

    <div class="mt-3">
        <h2 class="mb-4 fw-bolder"><?= trans("results"); ?></h2>

        <div id="resultsContainer">
            <?php if (!empty($quizResults)) {
                foreach ($quizResults as $result) {
                    echo view('admin/post/quiz/_result', ['postFormat' => $postFormat, 'result' => $result]);
                }
            } else {
                echo view('admin/post/quiz/_result', ['postFormat' => $postFormat, 'numItems' => 0]);
            } ?>
        </div>

        <div class="d-flex justify-content-center mt-6 mb-10">
            <button type="button" class="btn btn-success btn-add-quiz-result" data-post-format="<?= esc($postFormat, 'attr'); ?>">
                <i class="ki-duotone ki-plus fs-2"></i>
                <?= trans("add_result"); ?>
            </button>
        </div>
    </div>


<?= $this->section('scripts'); ?>
    <script>
        const quizResults = <?= json_encode($jsResultArray, JSON_UNESCAPED_UNICODE); ?>;
    </script>
<?= $this->endSection(); ?>