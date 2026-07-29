<?= $this->extend($viewsPath . '/layout'); ?>
<?= $this->section('content'); ?>

<?php
$mainColumnClass = ($page->right_column_active == 1) ? 'col-sm-12 col-md-12 col-lg-8' : 'col-12';
?>

    <section class="section section-page">
        <div class="container-xl">
            <div class="row">

                <?php if ($page->breadcrumb_active == 1): ?>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= langBaseUrl(); ?>"><?= trans("home"); ?></a></li>
                            <li class="breadcrumb-item active"><?= esc($page->title); ?></li>
                        </ol>
                    </nav>
                <?php endif; ?>

                <div class="<?= $mainColumnClass; ?>">
                    <?php if ($page->title_active == 1) : ?>
                        <h1 class="page-title"><?= esc($page->title); ?></h1>
                    <?php endif; ?>

                    <div class="page-content mb-5">
                        <div class="entry-content">
                            <?= $page->page_content; ?>
                        </div>
                    </div>
                </div>

                <?php if ($page->right_column_active == 1): ?>
                    <div class="col-sm-12 col-md-12 col-lg-4">
                        <?= loadView('partials/_sidebar'); ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section>

<?= $this->endSection(); ?>