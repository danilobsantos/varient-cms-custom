<div class="card card-flush">
    <div class="card-header collapsible cursor-pointer rotate <?= !empty($post) ? 'collapsed' : 'active'; ?>" data-bs-toggle="collapse" data-bs-target="#seo_card_collapsible">
        <h3 class="card-title fw-bold fs-3"><?= trans("meta_options"); ?>&nbsp;<span class="text-gray-500 fw-normal">(<?= trans("optional"); ?>)</span></h3>
        <div class="card-toolbar rotate-180">
            <i class="ki-duotone ki-down fs-1"></i>
        </div>
    </div>
    <div id="seo_card_collapsible" class="collapse <?= empty($post) ? 'show' : ''; ?>">
        <div class="card-body pt-5">
            <div class="mb-6">
                <label class="form-label"><?= trans("meta_title"); ?></label>
                <input type="text" class="form-control" name="meta_title" placeholder="<?= trans("meta_title", "attr"); ?>" maxlength="255" value="<?= esc(old('meta_title', $post->meta_title ?? '')); ?>">
            </div>
            <div class="mb-6">
                <label class="form-label"><?= trans("meta_description"); ?></label>
                <textarea name="meta_description" class="form-control" placeholder="<?= trans("meta_description", "attr"); ?>" maxlength="500"><?= old('meta_description', $post->meta_description ?? ''); ?></textarea>
            </div>
            <div class="mb-6">
                <label class="form-label"><?= trans("meta_keywords"); ?></label>
                <input type="text" class="form-control mb-2" name="meta_keywords" placeholder="<?= trans("meta_keywords", "attr"); ?>" maxlength="255" value="<?= esc(old('meta_keywords', $post->meta_keywords ?? '')); ?>">
                <div class="text-muted fs-7"><?= trans("meta_keywords_exp"); ?></div>
            </div>
        </div>
    </div>
</div>