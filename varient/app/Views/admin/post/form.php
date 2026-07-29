<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$postViews = [
        'recipe'            => [
                "admin/post/recipe/_recipe_ingredient",
                "admin/post/_list_items",
        ],
        'sorted_list'       => [
                "admin/post/_list_items",
        ],
        'gallery'           => [
                "admin/post/_list_items",
        ],
        'table_of_contents' => [
                "admin/post/_list_items",
        ],
        'trivia_quiz'       => [
                "admin/post/quiz/_questions",
                "admin/post/quiz/_results",
        ],
        'personality_quiz'  => [
                "admin/post/quiz/_results",
                "admin/post/quiz/_questions",
        ],
        'poll'              => [
                "admin/post/quiz/_questions",
        ],
];
?>

    <form id="form-post" action="<?= $action ?>" method="post" class="form kt-form d-flex flex-column flex-xl-row gap-5 gap-xl-7 gap-xxl-10 mb-5" enctype="multipart/form-data">
        <?= csrf_field(); ?>

        <input type="hidden" name="post_format" value="<?= esc($postFormat); ?>">

        <?php if (!empty($post)): ?>
            <input type="hidden" name="id" value="<?= esc($post->id); ?>">
        <?php endif; ?>

        <div class="w-100 flex-xl-row-auto w-xl-300px w-xxl-325px">
            <?= view("admin/post/sidebar/_sidebar"); ?>
        </div>

        <div class="d-flex flex-column flex-row-fluid gap-5 gap-xl-7 gap-xxl-10">

            <?= view("admin/post/general/_general"); ?>

            <?php if (in_array($postFormat, ['event'])): ?>
                <?= view("admin/post/event/_highlights_schedule"); ?>
                <?= view("admin/post/event/_speakers"); ?>
                <?= view("admin/post/event/_location"); ?>
                <?= view("admin/post/event/_registration"); ?>
            <?php endif; ?>

            <?php if (!in_array($postFormat, ['trivia_quiz', 'personality_quiz', 'poll'])): ?>
                <?= view("admin/post/general/_faq"); ?>
            <?php endif; ?>

            <?= view("admin/post/_meta"); ?>

            <?php if (!empty($postViews[$postFormat])): ?>
                <?php foreach ($postViews[$postFormat] as $viewPath): ?>
                    <?= view($viewPath); ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <?= view("admin/post/_publish"); ?>

        </div>
    </form>

<?= view("admin/media/file_manager", ['isModal' => true]); ?>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
    <script src="<?= base_url('assets/admin/js/quiz.js'); ?>"></script>
<?= $this->endSection(); ?>