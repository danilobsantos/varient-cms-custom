<?php if ($premiumMembership->status && hasPermission('manage_all_posts')):
    $isPremium = (int)($premiumObject->is_premium ?? 0);
    $isExclusive = (int)($premiumObject->is_exclusive ?? 0);
    $exclusivePrice = $premiumObject->exclusive_price ?? $config->premium_membership_settings->default_content_price ?? ''; ?>
    <div class="card card-flush py-4">
        <div class="card-header">
            <div class="card-title">
                <h2><?= trans("premium"); ?></h2>
            </div>
        </div>
        <div class="card-body pt-5">
            <div class="d-flex flex-column gap-8">

                <?php if ($premiumMembership->subscriptionMode === 'selective' && $premiumMembership->subscriptionStatus): ?>
                    <div class="fv-row">
                        <div class="d-flex flex-stack align-items-center mb-2">
                            <div class="me-5">
                                <label class="fs-6 fw-semibold"><?= $premiumObjectType == 'category' ? trans("premium_category") : trans("premium_content"); ?></label>
                            </div>
                            <?= formSwitch('is_premium', $isPremium); ?>
                        </div>
                        <div class="form-text text-gray-600"><?= $premiumObjectType == 'category' ? trans("premium_category_exp") : trans("premium_content_exp"); ?></div>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="is_premium" value="<?= esc($isPremium); ?>">
                <?php endif; ?>

                <?php if ($premiumMembership->exclusiveSaleStatus): ?>
                    <div class="fv-row">
                        <div class="d-flex flex-stack align-items-center mb-2">
                            <div class="me-5">
                                <label class="fs-6 fw-semibold"><?= $premiumObjectType == 'category' ? trans("exclusive_category") : trans("exclusive_content"); ?></label>
                            </div>
                            <?= formSwitch('is_exclusive', $isExclusive); ?>
                        </div>
                        <div class="form-text text-gray-600"><?= $premiumObjectType == 'category' ? trans("exclusive_category_exp") : trans("exclusive_content_exp"); ?></div>
                    </div>

                    <div class="fv-row d-none" id="exclusivePriceContainer">
                        <label class="form-label"><?= trans("price"); ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><?= esc($config->currency_settings->symbol); ?></span>
                            <input type="number" name="exclusive_price" value="<?= esc($exclusivePrice); ?>"
                                   class="form-control" placeholder="E.g. 3" min="0" max="10000000000" step="0.01"/>
                        </div>
                    </div>

                <?php else: ?>
                    <input type="hidden" name="is_exclusive" value="<?= esc($isExclusive); ?>">
                    <input type="hidden" name="exclusive_price" value="<?= esc($exclusivePrice); ?>">
                <?php endif; ?>

                <?php if ($premiumObjectType == 'category'): ?>
                    <div class="form-check form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" name="apply_to_subcategories" value="1" id="applySubcategoriesCheck"/>
                        <label class="form-check-label text-gray-600 fs-7" for="applySubcategoriesCheck">
                            <?= trans("apply_setting_to_subcategories"); ?>
                        </label>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <?= $this->section('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const exclusiveSwitch = document.querySelector('input[type="checkbox"][name="is_exclusive"]');
            const priceContainer = document.getElementById('exclusivePriceContainer');

            if (exclusiveSwitch && priceContainer) {
                if (exclusiveSwitch.checked) {
                    priceContainer.classList.remove('d-none');
                }

                exclusiveSwitch.addEventListener('change', function () {
                    if (this.checked) {
                        priceContainer.classList.remove('d-none');
                    } else {
                        priceContainer.classList.add('d-none');
                    }
                });
            }
        });
    </script>
    <?= $this->endSection(); ?>

<?php endif; ?>