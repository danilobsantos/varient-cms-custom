<?php $numFiltersApplied = 0;
if (!empty($filters)): ?>
    <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
        <div class="px-7 py-5">
            <div class="fs-5 text-gray-900 fw-bold"><?= trans("filter_options"); ?></div>
        </div>
        <div class="separator border-gray-200"></div>
        <div class="px-7 py-5" data-kt-subscription-table-filter="form">

            <?php if (in_array('id', $filters)):
                $id = (int)inputGet('id');
                $numFiltersApplied += !empty($id) ? 1 : 0; ?>
                <div class="mb-6">
                    <label class="form-label fs-6 fw-semibold"><?= trans("id"); ?>:</label>
                    <div class="input-group input-group-solid mb-5">
                        <span class="input-group-text">#</span>
                        <input type="number" name="id" value="<?= $id > 0 ? esc($id) : ''; ?>" class="form-control form-control-solid" placeholder="E.g. 45" min="0" max="10000000000" step="1"/>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (in_array('language', $filters)):
                $selectedLangId = (int)inputGet('lang_id');
                $numFiltersApplied += !empty($selectedLangId) ? 1 : 0; ?>
                <div class="mb-6">
                    <label class="form-label fs-6 fw-semibold"><?= trans("language"); ?>:</label>
                    <select name="lang_id" class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="true" data-hide-search="true"
                            <?php if (in_array('gallery_album', $filters)):
                                echo 'onchange="getAlbumsByLang(this.value);"';
                            endif; ?>>
                        <option></option>
                        <?php foreach ($activeLanguages as $language): ?>
                            <option value="<?= $language->id; ?>" <?= $selectedLangId == $language->id ? 'selected' : ''; ?>><?= esc($language->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if (in_array('gallery_album', $filters)):
                $selectedAlbumId = (int)inputGet('album_id');
                $numFiltersApplied += !empty($selectedAlbumId) ? 1 : 0; ?>
                <div class="mb-6">
                    <label class="form-label fs-6 fw-semibold"><?= trans("album"); ?>:</label>
                    <select name="album_id" id="albums" class="form-select form-select-solid fw-bold" onchange="getGalleryCategoriesByAlbum(this.value);" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="true" data-hide-search="true">
                        <option></option>
                        <?php if (!empty($albums)):
                            foreach ($albums as $item):?>
                                <option value="<?= $item->id; ?>" <?= $selectedAlbumId == $item->id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
                            <?php endforeach;
                        endif; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if (in_array('gallery_category', $filters)):
                $selectedCategoryId = (int)inputGet('category_id');
                $numFiltersApplied += !empty($selectedCategoryId) ? 1 : 0; ?>
                <div class="mb-6">
                    <label class="form-label fs-6 fw-semibold"><?= trans("category"); ?>:</label>
                    <select name="category_id" id="categories" class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="true" data-hide-search="true">
                        <option></option>
                        <?php if (!empty($categories)):
                            foreach ($categories as $item):?>
                                <option value="<?= $item->id; ?>" <?= $selectedCategoryId == $item->id ? 'selected' : ''; ?>><?= esc($item->name); ?></option>
                            <?php endforeach;
                        endif; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if (in_array('status', $filters)):
                $selectedStatus = inputGet('status');
                $numFiltersApplied += !empty($selectedStatus) ? 1 : 0; ?>
                <div class="mb-6">
                    <label class="form-label fs-6 fw-semibold"><?= trans("status"); ?>:</label>
                    <select name="status" class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="true" data-kt-subscription-table-filter="status" data-hide-search="true">
                        <option></option>
                        <option value="active" <?= $selectedStatus == 'active' ? 'selected' : ''; ?>><?= trans("active"); ?></option>
                        <option value="inactive" <?= $selectedStatus == 'inactive' ? 'selected' : ''; ?>><?= trans("inactive"); ?></option>
                    </select>
                </div>
            <?php endif; ?>

            <?php if (in_array('user_role', $filters)):
                $roleId = inputGet('role_id');
                $numFiltersApplied += !empty($roleId) ? 1 : 0; ?>
                <div class="mb-6">
                    <label class="form-label fs-6 fw-semibold"><?= trans("role"); ?>:</label>
                    <select name="role_id" class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="true" data-hide-search="true">
                        <option></option>
                        <?php if (!empty($roles)):
                            foreach ($roles as $role):
                                $roleName = '';
                                if (!empty($role)) {
                                    $roleName = getLocalizedObjectValue($role->role_name, $activeLang->id, 'name');
                                } ?>
                                <option value="<?= $role->id; ?>" <?= $roleId == $role->id ? 'selected' : ''; ?>><?= esc($roleName); ?></option>
                            <?php endforeach;
                        endif; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if (in_array('user_status', $filters)):
                $userStatus = inputGet('user_status');
                $numFiltersApplied += in_array((string)$userStatus, ['0', '1'], true) ? 1 : 0; ?>
                <div class="mb-6">
                    <label class="form-label fs-6 fw-semibold"><?= trans("status"); ?>:</label>
                    <select name="user_status" class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="true" data-kt-subscription-table-filter="status" data-hide-search="true">
                        <option></option>
                        <option value="1" <?= (string)$userStatus === '1' ? 'selected' : ''; ?>><?= trans("active"); ?></option>
                        <option value="0" <?= (string)$userStatus === '0' ? 'selected' : ''; ?>><?= trans("banned"); ?></option>
                    </select>
                </div>
            <?php endif; ?>

            <?php if (in_array('email_status', $filters)):
                $emailStatus = inputGet('email_status');
                $numFiltersApplied += in_array((string)$emailStatus, ['0', '1'], true) ? 1 : 0; ?>
                <div class="mb-6">
                    <label class="form-label fs-6 fw-semibold"><?= trans("email_status"); ?>:</label>
                    <select name="email_status" class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="true" data-kt-subscription-table-filter="status" data-hide-search="true">
                        <option></option>
                        <option value="1" <?= (string)$emailStatus === '1' ? 'selected' : ''; ?>><?= trans("verified"); ?></option>
                        <option value="0" <?= (string)$emailStatus === '0' ? 'selected' : ''; ?>><?= trans("unverified"); ?></option>
                    </select>
                </div>
            <?php endif; ?>

            <?php if (in_array('reward_system', $filters)):
                $rewardStatus = inputGet('reward_system');
                $numFiltersApplied += in_array((string)$rewardStatus, ['0', '1'], true) ? 1 : 0; ?>
                <div class="mb-6">
                    <label class="form-label fs-6 fw-semibold"><?= trans("reward_system"); ?>:</label>
                    <select name="reward_system" class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="true" data-kt-subscription-table-filter="status" data-hide-search="true">
                        <option></option>
                        <option value="1" <?= (string)$rewardStatus === '1' ? 'selected' : ''; ?>><?= trans("active"); ?></option>
                        <option value="0" <?= (string)$rewardStatus === '0' ? 'selected' : ''; ?>><?= trans("inactive"); ?></option>
                    </select>
                </div>
            <?php endif; ?>

            <?php if (in_array('transaction_type', $filters)):
                $transactionType = inputGet('transaction_type');
                $numFiltersApplied += in_array((string)$transactionType, ['subscription', 'content'], true) ? 1 : 0; ?>
                <div class="mb-6">
                    <label class="form-label fs-6 fw-semibold"><?= trans("transaction_type"); ?>:</label>
                    <select name="transaction_type" class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="true" data-kt-subscription-table-filter="status" data-hide-search="true">
                        <option></option>
                        <option value="subscription" <?= (string)$transactionType === 'subscription' ? 'selected' : ''; ?>><?= trans("subscription"); ?></option>
                        <option value="content" <?= (string)$transactionType === 'content' ? 'selected' : ''; ?>><?= trans("content"); ?></option>
                    </select>
                </div>
            <?php endif; ?>

            <?php if (in_array('payment_method', $filters)):
                $paymentMethods = $paymentMethods ?? [];
                $paymentMethod = inputGet('payment_method');
                $numFiltersApplied += !empty($paymentMethod) ? 1 : 0; ?>
                <div class="mb-6">
                    <label class="form-label fs-6 fw-semibold"><?= trans("payment_method"); ?>:</label>
                    <select name="payment_method" class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="true" data-kt-subscription-table-filter="status" data-hide-search="true">
                        <option></option>
                        <?php foreach ($paymentMethods as $method): ?>
                            <option value="<?= $method; ?>" <?= (string)$method === (string)$paymentMethod ? 'selected' : ''; ?>><?= trans($method, true); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if (in_array('date_range', $filters)):
                $startDate = inputGet('start_date');
                $endDate = inputGet('end_date');
                $numFiltersApplied += !empty($startDate) && !empty($endDate) ? 1 : 0; ?>
                <div class="mb-6">
                    <label class="form-label form-label-sm"><?= trans("date_range"); ?></label>
                    <div class="input-group input-group-solid">
                    <span class="input-group-text">
                        <i class="ki-duotone ki-calendar-8 fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i>
                    </span>
                        <input class="form-control form-control-solid" placeholder="<?= trans("select_date_range", "attr"); ?>" id="transaction_date_range" autocomplete="off"/>
                    </div>
                    <input type="hidden" name="start_date" id="filter_start_date" value="<?= esc($startDate); ?>">
                    <input type="hidden" name="end_date" id="filter_end_date" value="<?= esc($endDate); ?>">
                </div>
            <?php endif; ?>

            <?php if (array_key_exists('search', $filters)):
                $searchParam = inputGet('q');
                $numFiltersApplied += !empty($searchParam) ? 1 : 0; ?>
                <div class="mb-6">
                    <label class="form-label fs-6 fw-semibold"><?= trans("search"); ?>:</label>
                    <input type="text" name="q" class="form-control form-control-solid" value="<?= esc($searchParam ?? ''); ?>" placeholder="<?= esc($filters['search'] ?? '', "attr"); ?>...">
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-end">
                <?php if (!empty($filterPageUrl)): ?>
                    <a href="<?= esc($filterPageUrl); ?>" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6"><?= trans("reset"); ?></a>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary fw-semibold px-6" data-kt-menu-dismiss="true" data-kt-subscription-table-filter="filter"><?= trans("apply"); ?></button>
            </div>

        </div>
    </div>
<?php endif; ?>

    <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
        <i class="ki-duotone ki-filter fs-2">
            <span class="path1"></span>
            <span class="path2"></span>
        </i><?= trans("filter"); ?>
        <?php if ($numFiltersApplied > 0): ?>
            &nbsp;&nbsp;<span class="badge badge-primary fw-bold fs-7"><?= $numFiltersApplied; ?></span>
        <?php endif; ?>
    </button>

<?= $this->section('scripts'); ?>

<?php if (in_array('date_range', $filters)): ?>
    <script>
        $(document).ready(function () {
            var dateInput = $('#transaction_date_range');

            dateInput.daterangepicker({
                autoUpdateInput: false,
                autoApply: true,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                }
            });

            dateInput.on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' / ' + picker.endDate.format('YYYY-MM-DD'));
                $('#filter_start_date').val(picker.startDate.format('YYYY-MM-DD'));
                $('#filter_end_date').val(picker.endDate.format('YYYY-MM-DD'));
            });

            dateInput.on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
                $('#filter_start_date').val('');
                $('#filter_end_date').val('');
            });

            var activeStart = $('#filter_start_date').val();
            var activeEnd = $('#filter_end_date').val();

            if (activeStart && activeEnd) {
                dateInput.val(activeStart + ' / ' + activeEnd);
                dateInput.data('daterangepicker').setStartDate(activeStart);
                dateInput.data('daterangepicker').setEndDate(activeEnd);
            }
        });
    </script>
<?php endif; ?>

<?= $this->endSection(); ?>