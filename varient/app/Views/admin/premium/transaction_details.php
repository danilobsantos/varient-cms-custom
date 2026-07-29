<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
// User
$customerName = !empty($user) ? $user->username : '';
$avatarPath = !empty($user) ? $user->avatar : '';
$avatarStorage = !empty($user) ? $user->storage_avatar : '';
?>

    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">

        <div class="col-md-12 col-lg-6">
            <div class="card card-flush h-100">
                <div class="card-header pt-5">
                    <div class="card-title align-items-start flex-column">
                        <h3><?= trans("payment_details"); ?></h3>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="d-flex flex-column gap-5">

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-semibold"><?= trans("transaction_id"); ?></span>
                            <span class="fw-bold text-gray-800">#<?= esc($transaction->id); ?></span>
                        </div>

                        <div class="separator separator-dashed"></div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-semibold"><?= trans("order_token"); ?></span>
                            <span class="fw-bold text-gray-800 fs-7">#<?= esc($order->order_token); ?></span>
                        </div>

                        <div class="separator separator-dashed"></div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-semibold"><?= trans("gateway_transaction_id"); ?></span>
                            <?php if (!empty($transaction->gateway_transaction_id)): ?>
                                <span class="fw-bold fs-7 text-primary font-monospace bg-light-primary py-1 px-2 rounded">
                                    <?= esc($transaction->gateway_transaction_id); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted fs-7">-</span>
                            <?php endif; ?>
                        </div>

                        <div class="separator separator-dashed"></div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-semibold"><?= trans("payment_method"); ?></span>
                            <span class="fw-bold text-gray-800 text-capitalize">
                                <?= trans($transaction->payment_method, true); ?>
                            </span>
                        </div>

                        <div class="separator separator-dashed"></div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-semibold"><?= trans("status"); ?></span>
                            <span class="badge badge-light-success px-4 py-2">
                                <?= trans($transaction->status, true); ?>
                            </span>
                        </div>

                        <div class="separator separator-dashed"></div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-semibold"><?= trans("customer_ip_address"); ?></span>
                            <span class="fw-bold text-gray-800">
                                <?= esc($order->ip_address ?? '-'); ?>
                            </span>
                        </div>
                        <div class="separator separator-dashed"></div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-semibold"><?= trans("date"); ?></span>
                            <span class="fw-bold text-gray-800">
                                <?= $transaction->created_at; ?>
                            </span>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-lg-6">
            <div class="card card-flush h-100">
                <div class="card-header pt-5">
                    <div class="card-title align-items-start flex-column">
                        <h3><?= trans("customer_details"); ?></h3>
                    </div>
                </div>

                <div class="card-body pt-5">
                    <div class="d-flex flex-center flex-column mb-8">
                        <div class="symbol symbol-90px symbol-circle mb-4">
                            <img src="<?= getUserAvatar($avatarPath, $avatarStorage); ?>" alt="Avatar" class="w-90px h-90px"/>
                        </div>
                        <span class="fs-4 text-gray-800 fw-bold mb-1">
                            <?= esc($order->billing_name ?? $customerName); ?>
                        </span>
                        <span class="text-gray-500 fw-semibold">
                            <?= esc($order->billing_email ?? '-'); ?>
                        </span>
                    </div>

                    <div class="d-flex flex-column gap-5">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-semibold"><?= trans("user_id"); ?></span>
                            <span class="fw-bold text-gray-800">
                                <?= esc($order->user_id); ?>
                            </span>
                        </div>

                        <div class="separator separator-dashed"></div>

                        <?php if (!empty($order->billing_company_name)): ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted fw-semibold"><?= trans("company_name"); ?></span>
                                <span class="fw-bold text-gray-800"><?= esc($order->billing_company_name ?? '-'); ?></span>
                            </div>

                            <div class="separator separator-dashed"></div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted fw-semibold"><?= trans("tax_vat_number"); ?></span>
                                <span class="fw-bold text-gray-800"><?= esc($order->billing_tax_number ?? '-'); ?></span>
                            </div>

                            <div class="separator separator-dashed"></div>
                        <?php endif; ?>

                        <div class="d-flex flex-column">
                            <span class="text-muted fw-semibold mb-2"><?= trans("billing_address"); ?></span>
                            <span class="fw-bold text-gray-800 fs-7">
                                <?php
                                $addressParts = array_filter([
                                        $order->billing_address,
                                        $order->billing_city,
                                        $order->billing_zip_code,
                                        $order->billing_country
                                ]);
                                echo !empty($addressParts) ? esc(implode(', ', $addressParts)) : '-';
                                ?>
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <div class="d-flex flex-column gap-7 gap-lg-10">

        <div class="card card-flush py-4">
            <div class="card-header pt-5">
                <div class="card-title align-items-start flex-column">
                    <h3><?= trans("order_summary"); ?></h3>
                </div>
            </div>

            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
                        <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-300px"><?= trans("item_id"); ?></th>
                            <th class="min-w-300px"><?= trans("title"); ?></th>
                            <th class="min-w-100px"><?= trans("item_type"); ?></th>
                            <th class="min-w-100px text-end"><?= trans("amount"); ?></th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                        <tr>
                            <td>
                                <div class="fw-bold text-gray-800 fs-5 mb-1">
                                    #<?= esc($order->item_id ?? ''); ?>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-gray-800 fs-5 mb-1">
                                    <?= esc($order->item_title ?? ''); ?>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php $orderType = $order->item_type ?? ''; ?>

                                    <?php if (in_array($orderType, ['subscription', 'content'])): ?>
                                        <span class="badge badge-light-primary fw-bold px-3 py-1 text-capitalize">
                                            <?= trans($order->item_type, true); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($order->billing_cycle) && $orderType === 'subscription'): ?>
                                        <div class="d-flex align-items-center">
                                            <span class="bullet bullet-dot bg-gray-300 h-6px w-6px me-2"></span>
                                            <span class="text-muted fs-7 fw-semibold me-1"><?= trans("billing_cycle"); ?>:</span>
                                            <span class="text-gray-800 fs-7 fw-bold text-capitalize"><?= esc($order->billing_cycle); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td class="text-end fw-bold text-gray-800 fs-5">
                                <?= esc($order->subtotal) . ' ' . esc($transaction->currency); ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div class="separator separator-dashed my-5"></div>

                <div class="d-flex justify-content-end">
                    <div class="mw-300px w-100">

                        <div class="d-flex justify-content-between mb-4">
                            <span class="text-muted fw-semibold fs-6"><?= trans("subtotal"); ?></span>
                            <span class="fw-bold text-gray-800 fs-6">
                            <?= esc($order->subtotal ?? $order->total_amount) . ' ' . esc($transaction->currency); ?>
                        </span>

                        </div>

                        <?php if (!empty($order->tax) && $order->tax > 0): ?>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="text-muted fw-semibold fs-6"><?= trans("tax"); ?> (<?= esc($order->tax_rate); ?>%)</span>
                                <span class="fw-bold text-gray-800 fs-6"><?= esc($order->tax ?? $order->tax) . ' ' . esc($transaction->currency); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($order->transaction_fee) && $order->transaction_fee > 0): ?>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="text-muted fw-semibold fs-6"><?= trans("transaction_fee"); ?> (<?= esc($order->transaction_fee_rate); ?>%)</span>
                                <span class="fw-bold text-gray-800 fs-6"><?= esc($order->transaction_fee ?? $order->transaction_fee) . ' ' . esc($transaction->currency); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="separator separator-dashed mb-4"></div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-gray-800 fs-4"><?= trans("total_paid"); ?></span>
                            <span class="fw-bolder text-success fs-2"><?= esc($order->total_amount) . ' ' . esc($transaction->currency); ?></span>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>

<?= $this->endSection(); ?>