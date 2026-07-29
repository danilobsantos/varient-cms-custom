<script>var hostUrl = "assets/";</script>
<script src="<?= base_url('assets/admin/plugins/global/plugins.bundle.min.js'); ?>"></script>
<script src="<?= base_url('assets/admin/js/scripts.bundle.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/tinymce/tinymce.min.js'); ?>"></script>
<script src="<?= base_url('assets/admin/js/main.js'); ?>"></script>

<?php if ($activeLang->short_form === 'pt_br' || $activeLang->short_form === 'pt'): ?>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/pt.js"></script>
    <script>
        if (typeof flatpickr !== 'undefined' && flatpickr.l10ns && flatpickr.l10ns.pt) {
            flatpickr.localize(flatpickr.l10ns.pt);
        }
    </script>
<?php endif; ?>

<style>
    .image-input-wrapper {
        background-image: url('<?= base_url("assets/admin/media/blank.svg"); ?>');
    }

    [data-bs-theme="dark"] .image-input-wrapper {
        background-image: url('<?= base_url("assets/admin/media/blank-dark.svg"); ?>');
    }
</style>

<?php if (empty($disableToastr)): ?>
    <?= view("admin/includes/_toastr"); ?>
<?php endif; ?>

<?= $this->renderSection('scripts'); ?>

</body>
</html>
