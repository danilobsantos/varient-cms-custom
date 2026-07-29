<?php
// Convert extensions array to accept string
$accept = isset($extensions) && is_array($extensions)
    ? implode(', ', array_map(fn($ext) => '.' . ltrim($ext, '.'), $extensions))
    : '';

$label = !empty($label) ? $label : trans("select_file");
?>

<div class="file-upload">
    <input type="file" name="<?= esc($name ?? 'file'); ?>" accept="<?= esc($accept); ?>" class="d-none" data-upload-input>
    <button type="button" class="btn btn-sm btn-light-info" data-upload-button>
        <i class="ki-duotone ki-file-up fs-2">
            <span class="path1"></span><span class="path2"></span>
        </i>
        <?= esc($label); ?>
    </button>
    <div class="mt-2 text-muted" data-upload-filename></div>
</div>