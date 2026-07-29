<?php $acceptFormats = !empty($fnAccept) ? $fnAccept : ['jpg', 'jpeg', 'webp', 'png']; ?>

<div class="image-input image-input-outline" data-kt-image-input="true" <?= !empty($fnDarkBg) ? 'style="background-color: #eee !important;"' : ''; ?>>

    <div class="image-input-wrapper <?= !empty($fnSizeClass) ? esc($fnSizeClass) : 'w-175px h-175px'; ?>" <?= !empty($fnImageUrl) ? 'style="background-image: url(' . esc($fnImageUrl) . ')"' : '' ?>></div>

    <label class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow gap-0"
           data-kt-image-input-action="change"
           data-bs-dismiss="click">
        <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i>

        <input type="file" name="<?= !empty($fnName) ? esc($fnName) : 'file'; ?>" accept="<?= esc(implode(', ', array_map(fn($ext) => '.' . ltrim($ext, '.'), $acceptFormats))); ?>"/>
        <?php if ($fnDelete): ?>
            <input type="hidden" name="<?= $fnName; ?>_remove"/>
        <?php endif; ?>
    </label>

    <span class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
          data-kt-image-input-action="cancel"
          data-bs-dismiss="click">
        <i class="ki-outline ki-cross fs-3"></i>
    </span>

    <?php if (!empty($fnDelete) && !empty($fnImageUrl)): ?>
        <span class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
              data-kt-image-input-action="remove"
              data-bs-dismiss="click">
        <i class="ki-outline ki-cross fs-3"></i>
    </span>
    <?php endif; ?>
</div>

<div class="text-muted text-start fs-7">
    <?php $filtered = array_filter($acceptFormats, fn($ext) => $ext !== 'jpeg');
    $acceptString = implode(', ', array_map(fn($ext) => '*.' . ltrim($ext, '.'), $filtered));

    echo $acceptString; ?>
</div>