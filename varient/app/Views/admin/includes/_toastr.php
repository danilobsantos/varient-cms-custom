<?php $session = session();
if ($session->getFlashdata('error')): ?>
    <script>
        toastr.error("<?= esc($session->getFlashdata('error'), "js"); ?>", "<?= trans("error", "js"); ?>");
    </script>
<?php elseif ($session->getFlashdata('success')): ?>
    <script>
        toastr.success("<?= esc($session->getFlashdata('success'), "js"); ?>", "<?= trans("success", "js"); ?>");
    </script>
<?php endif; ?>