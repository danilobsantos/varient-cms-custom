<div class="row g-4">
    <div class="col-12">
        <label class="form-label"><?= trans("full_name"); ?> <span class="text-danger">*</span></label>
        <input type="text" name="full_name" value="<?= esc($billing->full_name ?? ''); ?>" class="form-control form-input" placeholder="<?= trans("full_name", "attr"); ?>" required>
    </div>

    <div class="col-12">
        <label class="form-label"><?= trans("email_address"); ?> <span class="text-danger">*</span></label>
        <input type="email" name="email" value="<?= esc($billing->email ?? ''); ?>" class="form-control form-input" placeholder="<?= trans("email_address", "attr"); ?>" required>
    </div>

    <div class="col-12">
        <label class="form-label"><?= trans("address"); ?> <span class="text-danger">*</span></label>
        <input type="text" name="address" value="<?= esc($billing->address ?? ''); ?>" class="form-control form-input" placeholder="<?= trans("address", "attr"); ?>" required>
    </div>

    <div class="col-sm-6">
        <label class="form-label"><?= trans("country"); ?> <span class="text-danger">*</span></label>
        <select name="country_id" class="form-select" required>
            <option value="" selected><?= trans("select"); ?></option>
            <?php foreach ($countries as $country): ?>
                <option value="<?= esc($country->id); ?>" <?= (int)$country->id === (int)($billing->country_id ?? 0) ? 'selected' : ''; ?>><?= esc($country->name); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-sm-6">
        <label class="form-label"><?= trans("city"); ?> / <?= trans("state"); ?> <span class="text-danger">*</span></label>
        <input type="text" name="city_state" value="<?= esc($billing->city_state ?? ''); ?>" class="form-control form-input" placeholder="<?= trans("city", "attr"); ?> / <?= trans("state", "attr"); ?>" required>
    </div>

    <div class="col-sm-6">
        <label class="form-label"><?= trans("zip_postal_code"); ?> <span class="text-danger">*</span></label>
        <input type="text" name="zip_postal_code" value="<?= esc($billing->zip_postal_code ?? ''); ?>" class="form-control form-input" placeholder="<?= trans("zip_postal_code", "attr"); ?>" required>
    </div>

    <div class="col-sm-6">
        <label class="form-label">
            <?= trans("company_name"); ?>
            <span class="optional-tag">(<?= trans("optional"); ?>)</span>
        </label>
        <input type="text" name="company_name" value="<?= esc($billing->company_name ?? ''); ?>" class="form-control form-input" placeholder="<?= trans("company_name", "attr"); ?>">
    </div>

    <div class="col-12">
        <label class="form-label">
            <?= trans("tax_vat_number"); ?> <span class="optional-tag">(<?= trans("optional"); ?>)</span>
        </label>
        <input type="text" name="tax_vat_number" value="<?= esc($billing->tax_vat_number ?? ''); ?>" class="form-control form-input" placeholder="<?= trans("tax_vat_number", "attr"); ?>">
    </div>
</div>