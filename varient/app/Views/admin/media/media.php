<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?= view("admin/media/file_manager"); ?>

<?php if ($source === 'content_image'): ?>
    <?= renderAlert('danger', 'info', trans("content_images"), trans("content_images_upload_exp")); ?>
<?php endif; ?>

    <style>
        #fileManagerModal {
            height: 600px;
        }

        @media (min-width: 1400px) {
            #fileManagerModal {
                height: 890px;
            }
        }
    </style>

    <script>
        const source = "<?= esc($source); ?>";
        const viewMode = source == 'content_image' ? 'image' : source;
        const mediaConfig = {
            selectMode: 'multi',
            viewMode: viewMode,
            dataSource: source,
            allowedExtensions: "<?= esc($allowedExtensions); ?>",
            isPageMode: true
        };
    </script>

<?= $this->endSection(); ?>