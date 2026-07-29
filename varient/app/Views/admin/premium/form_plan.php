<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$planId = !empty($plan) ? $plan->id : '';
$status = !empty($plan) ? (int)$plan->status : 1;
$planNameJson = !empty($plan) ? $plan->plan_name : '';
$price = !empty($plan) ? numToDecimal($plan->price) : 0;
$sortOrder = !empty($plan) ? (int)($plan->sort_order) : 1;
$isLifetime = !empty($plan) ? (int)$plan->is_lifetime : 0;
$isPopular = !empty($plan) ? (int)$plan->is_popular : 0;
$isAdFree = !empty($plan) ? (int)$plan->is_ad_free : 0;
$isFreePlan = !empty($plan) ? (int)$plan->is_free_plan : 0;
$planBadgeId = !empty($plan) ? $plan->badge_id : 0;

// Period Variables
$billingCycle = !empty($plan) ? $plan->billing_cycle : 'monthly';

// Decode features from the new database column
$allFeatures = (!empty($plan) && !empty($plan->features)) ? json_decode($plan->features, true) : [];

$langIds = [];
foreach ($activeLanguages as $lang) {
    $langIds[] = $lang->id;
}

// Calculate the maximum number of features across ALL languages to prevent index shifting
$maxFeaturesCount = 0;
if (!empty($allFeatures)) {
    foreach ($allFeatures as $langFeatures) {
        if (is_array($langFeatures)) {
            $maxFeaturesCount = max($maxFeaturesCount, count($langFeatures));
        }
    }
}
$existingFeaturesCount = $maxFeaturesCount; // Set for JavaScript
?>

    <form action="<?= $action; ?>" method="post" class="form kt-form d-flex flex-column flex-xl-row gap-5 gap-xl-7 gap-xxl-10 mb-5 mb-xl-7 mb-xxl-10">
        <?= csrf_field(); ?>
        <input type="hidden" name="id" value="<?= $planId; ?>">

        <div class="w-100 flex-xl-row-auto w-xl-300px w-xxl-325px">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?= trans("settings"); ?></h2>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="d-flex flex-column gap-8">

                        <div class="fv-row">
                            <label class="required fs-6 fw-semibold mb-2"><?= trans("billing_cycle"); ?></label>
                            <select name="billing_cycle" id="select_billing_cycle" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <option value="daily" <?= $billingCycle === 'daily' ? 'selected' : ''; ?>><?= trans("daily"); ?></option>
                                <option value="weekly" <?= $billingCycle === 'weekly' ? 'selected' : ''; ?>><?= trans("weekly"); ?></option>
                                <option value="monthly" <?= $billingCycle === 'monthly' ? 'selected' : ''; ?>><?= trans("monthly"); ?></option>
                                <option value="yearly" <?= $billingCycle === 'yearly' ? 'selected' : ''; ?>><?= trans("yearly"); ?></option>
                            </select>
                            <div class="form-check form-check-custom mt-3">
                                <input class="form-check-input" type="checkbox" name="is_lifetime" value="1" id="modal_is_lifetime" <?= $isLifetime === 1 ? 'checked' : ''; ?>/>
                                <label class="form-check-label text-gray-600 fs-7 fw-bold" for="modal_is_lifetime"><?= trans("lifetime_plan"); ?> (<?= trans("never_expires"); ?>)</label>
                            </div>
                        </div>

                        <div class="fv-row">
                            <label class="required fs-6 fw-semibold mb-2"><?= trans("price"); ?></label>
                            <div class="input-group">
                                <span class="input-group-text"><?= esc($config->currency_settings->symbol); ?></span>
                                <input type="number" name="price" id="input_plan_price" value="<?= esc($price); ?>" class="form-control" placeholder="E.g. 2.99" min="0" max="10000000000" step="0.01" required/>
                            </div>
                            <div class="form-check form-check-custom mt-3">
                                <input class="form-check-input" type="checkbox" name="is_free_plan" value="1" id="checkbox_is_free_plan" <?= $isFreePlan === 1 ? 'checked' : ''; ?>/>
                                <label class="form-check-label text-gray-600 fs-7 fw-bold" for="checkbox_is_free_plan"><?= trans("free"); ?></label>
                            </div>
                        </div>

                        <div class="separator separator-dashed"></div>

                        <div class="fv-row">
                            <label class="form-label"><?= trans("user_profile_badge"); ?></label>
                            <select name="badge_id" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <option value="0"><?= trans("none"); ?></option>
                                <?php if (!empty($badges)):
                                    foreach ($badges as $badge):
                                        $badgeName = getLocalizedObjectValue($badge->badge_name, $activeLang->id, 'name'); ?>
                                        <option value="<?= $badge->id; ?>" <?= $badge->id === $planBadgeId ? 'selected' : ''; ?>><?= esc($badgeName); ?></option>
                                    <?php endforeach;
                                endif; ?>
                            </select>
                        </div>

                        <div class="fv-row">
                            <label class="required form-label"><?= trans("sort_order"); ?></label>
                            <input type="number" name="sort_order" class="form-control" placeholder="<?= trans('sort_order'); ?>" value="<?= esc($sortOrder); ?>" min="1" max="9999" step="1" required/>
                        </div>

                        <div class="fv-row">
                            <div class="d-flex flex-stack">
                                <div class="me-5">
                                    <label class="fs-6 fw-semibold"><?= trans("most_popular"); ?></label>
                                </div>
                                <?= formSwitch('is_popular', $isPopular); ?>
                            </div>
                        </div>

                        <div class="fv-row">
                            <div class="d-flex flex-stack">
                                <div class="me-5">
                                    <label class="fs-6 fw-semibold"><?= trans("ad_free_experience"); ?></label>
                                </div>
                                <?= formSwitch('is_ad_free', $isAdFree); ?>
                            </div>
                        </div>

                        <div class="fv-row">
                            <div class="d-flex flex-stack">
                                <div class="me-5">
                                    <label class="fs-6 fw-semibold"><?= trans("status"); ?></label>
                                </div>
                                <?= formSwitch('status', $status); ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-row-fluid gap-7 gap-xl-10">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?= trans("general"); ?></h2>
                    </div>
                </div>

                <div class="card-body pt-5">

                    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                        <?php foreach ($activeLanguages as $key => $lang): ?>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 <?= $key === 0 ? 'active' : ''; ?>"
                                   data-bs-toggle="tab" href="#lang_tab_<?= $lang->id; ?>">
                                    <?= esc($lang->name); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="tab-content" id="planTabContent">
                        <?php foreach ($activeLanguages as $key => $lang):
                            $name = getLocalizedObjectValue($planNameJson, $lang->id, 'name');
                            $desc = getLocalizedObjectValue($planNameJson, $lang->id, 'desc');

                            // Fetch features array for this specific language
                            $features = isset($allFeatures[$lang->id]) && is_array($allFeatures[$lang->id])
                                    ? $allFeatures[$lang->id]
                                    : [];
                            ?>
                            <div class="tab-pane fade <?= $key === 0 ? 'show active' : ''; ?>" id="lang_tab_<?= $lang->id; ?>" role="tabpanel">

                                <div class="mb-10">
                                    <label class="form-label required"><?= trans("plan_name"); ?></label>
                                    <input type="text" name="translations[<?= $lang->id; ?>][name]" class="form-control" value="<?= esc($name); ?>" placeholder="Enter plan name"/>
                                </div>

                                <div class="mb-10">
                                    <label class="form-label"><?= trans("description"); ?></label>
                                    <textarea name="translations[<?= $lang->id; ?>][description]" class="form-control" rows="3" placeholder="Briefly describe this plan"><?= esc($desc); ?></textarea>
                                </div>

                                <div class="mb-15">
                                    <label class="form-label d-block mb-4"><?= trans("plan_features"); ?></label>
                                    <div id="features_container_<?= $lang->id; ?>" class="dynamic-features-list">
                                        <?php
                                        for ($fIndex = 0; $fIndex < $maxFeaturesCount; $fIndex++):
                                            $featureText = $features[$fIndex] ?? '';
                                            ?>
                                            <div class="d-flex align-items-center gap-3 mb-3 feature-group-<?= $fIndex; ?>">
                                                <input type="text" name="translations[<?= $lang->id; ?>][features][]" class="form-control" value="<?= esc($featureText); ?>" placeholder="<?= trans("feature_detail", "attr"); ?>">
                                                <button type="button" class="btn btn-icon btn-light-danger btn-sm" onclick="removeFeatureGroup(<?= $fIndex; ?>)">
                                                    <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                </button>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-light-primary mt-3" onclick="addFeatureToAllLanguages()">
                                        <i class="ki-outline ki-plus fs-3"></i> <?= trans("add_new"); ?>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                    <span class="indicator-label"><?= !empty($plan) ? trans("save_changes") : trans("add_plan"); ?></span>
                    <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </form>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
    <script>
        /**
         * Synchronized Feature Management across multiple languages
         */
        const activeLanguageIds = <?= json_encode($langIds); ?>;
        const textPlaceholder = <?= json_encode(trans("feature_detail")); ?>;
        let featureGlobalIndex = <?= $existingFeaturesCount; ?>;

        /**
         * Appends a new input row to every language tab simultaneously
         */
        function addFeatureToAllLanguages() {
            activeLanguageIds.forEach(langId => {
                const container = document.getElementById('features_container_' + langId);
                const row = document.createElement('div');

                row.className = `d-flex align-items-center gap-3 mb-3 feature-group-${featureGlobalIndex}`;
                row.innerHTML = `
                <input type="text" name="translations[${langId}][features][]"
                       class="form-control"
                       placeholder="${textPlaceholder}">
                <button type="button" class="btn btn-icon btn-light-danger btn-sm"
                        onclick="removeFeatureGroup(${featureGlobalIndex})">
                    <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                </button>
            `;

                container.appendChild(row);
            });

            featureGlobalIndex++;
        }

        /**
         * Removes the targeted feature row across all language tabs
         */
        function removeFeatureGroup(index) {
            const allMatchingGroups = document.querySelectorAll('.feature-group-' + index);
            allMatchingGroups.forEach(element => {
                element.remove();
            });
        }

        /**
         * Dynamic Form Controls Logic (Lifetime & Free Plan)
         */
        document.addEventListener('DOMContentLoaded', function () {
            // Lifetime Logic Elements
            const lifetimeCheckbox = document.getElementById('modal_is_lifetime');
            const frequencySelect = document.getElementById('select_billing_cycle');

            // Free Plan Logic Elements
            const freePlanCheckbox = document.getElementById('checkbox_is_free_plan');
            const priceInput = document.getElementById('input_plan_price');

            /**
             * Handles Lifetime plan toggling
             */
            const handleLifetimeToggle = function () {
                if (lifetimeCheckbox.checked) {
                    frequencySelect.setAttribute('disabled', 'disabled');
                    frequencySelect.classList.add('bg-light');
                } else {
                    frequencySelect.removeAttribute('disabled');
                    frequencySelect.classList.remove('bg-light');
                }
            };

            /**
             * Handles Free Plan toggling
             */
            const handleFreePlanToggle = function () {
                if (freePlanCheckbox && freePlanCheckbox.checked) {
                    priceInput.value = '0';
                    priceInput.setAttribute('readonly', 'readonly');
                    priceInput.classList.add('bg-light');
                } else if (freePlanCheckbox) {
                    priceInput.removeAttribute('readonly');
                    priceInput.classList.remove('bg-light');
                }
            };

            // Initialize Event Listeners
            if (lifetimeCheckbox && frequencySelect) {
                lifetimeCheckbox.addEventListener('change', handleLifetimeToggle);
                handleLifetimeToggle(); // Initial check on load
            }

            if (freePlanCheckbox && priceInput) {
                freePlanCheckbox.addEventListener('change', handleFreePlanToggle);
                handleFreePlanToggle(); // Initial check on load
            }
        });
    </script>
<?= $this->endSection(); ?>