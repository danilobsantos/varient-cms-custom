<?php
$currency = esc($transaction->currency);
$invoicePrefix = $config->invoice_prefix ?? 'INV';
$invoiceNumber = $invoicePrefix . '-' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT);
$issueDate = date('F d, Y', strtotime($transaction->created_at));
$invoiceDetails = safeDecode($baseSettings->invoice_details ?? '');
$customerName = !empty($user) ? $user->username : '';
$billingName = esc($order->billing_name ?? $customerName);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= trans("invoice"); ?> <?= $invoiceNumber; ?></title>
    <link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">

    <style>
        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url('<?= base_url('assets/fonts/inter/Inter-Regular.woff2'); ?>') format('woff2');
        }

        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 500;
            font-display: swap;
            src: url('<?= base_url('assets/fonts/inter/Inter-Medium.woff2'); ?>') format('woff2');
        }

        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 600;
            font-display: swap;
            src: url('<?= base_url('assets/fonts/inter/Inter-SemiBold.woff2'); ?>') format('woff2');
        }

        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 700;
            font-display: swap;
            src: url('<?= base_url('assets/fonts/inter/Inter-Bold.woff2'); ?>') format('woff2');
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            -webkit-font-smoothing: antialiased;
        }

        .invoice-wrapper {
            max-width: 850px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            padding: 70px;
            position: relative;
            overflow: hidden;
        }

        .invoice-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: <?= esc($activeTheme->theme_color); ?>;
        }

        .text-muted-custom {
            color: #6b7280;
        }

        .text-label {
            letter-spacing: 0.05em;
            font-size: 0.75rem;
            color: #9ca3af;
            font-weight: 600;
            text-transform: uppercase;
        }

        .border-bottom-custom {
            border-bottom: 1px solid #e5e7eb;
        }

        .border-top-custom {
            border-top: 1px solid #e5e7eb;
        }

        .table > :not(caption) > * > * {
            padding: 1rem 0.5rem;
            border-bottom-color: #e5e7eb;
        }

        .table th {
            border-bottom: 2px solid #e5e7eb;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        @media print {
            body {
                background-color: #ffffff;
            }

            .invoice-wrapper {
                box-shadow: none;
                margin: 0 auto;
                padding: 20px 40px;
                border-radius: 0;
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

<div class="invoice-wrapper">
    <div class="row align-items-center mb-4">
        <div class="col-6">
            <?php $logoSize = getLogoSize(); ?>
            <img src="<?= getLogo('light'); ?>" class="logo js-logo-light" alt="<?= trans("app_name", "attr"); ?> Logo" width="<?= $logoSize->width; ?>" height="<?= $logoSize->height; ?>">
        </div>
        <div class="col-6 text-end">
            <h1 class="fw-bold m-0" style="color: #d1d5db; letter-spacing: -1px; text-transform: uppercase;"><?= trans("invoice"); ?></h1>
            <p class="text-muted-custom fw-semibold mt-1 mb-0"><?= $invoiceNumber; ?></p>
        </div>
    </div>

    <div class="row mb-5 pb-4 border-bottom-custom">
        <div class="col-sm-6 mb-4 mb-sm-0">
            <div class="mb-4">
                <div class="text-label mb-2"><?= trans("billed_by"); ?></div>
                <h6 class="fw-bold mb-1"><?= esc($invoiceDetails->company_name ?? ''); ?></h6>
                <div class="text-muted-custom fs-7">
                    <?= nl2br(esc($invoiceDetails->details ?? '')); ?>
                </div>
            </div>

            <div>
                <div class="text-label mb-2"><?= trans("billed_to"); ?></div>
                <h6 class="fw-bold mb-1"><?= $billingName; ?></h6>
                <div class="text-muted-custom fs-7">
                    <?php if (!empty($order->billing_company_name)): ?>
                        <span><?= esc($order->billing_company_name); ?></span><br>
                    <?php endif; ?>

                    <?php
                    $addressParts = array_filter([
                            $order->billing_address,
                            $order->billing_city,
                            $order->billing_zip_code,
                            $order->billing_country
                    ]);
                    echo !empty($addressParts) ? esc(implode(', ', $addressParts)) . '<br>' : '';
                    ?>

                    <?php if (!empty($order->billing_tax_number)): ?>
                        <?= trans("tax_vat_number"); ?>: <?= esc($order->billing_tax_number); ?><br>
                    <?php endif; ?>

                    <?= esc($order->billing_email ?? '-'); ?>
                </div>
            </div>
        </div>

        <div class="col-sm-6 text-sm-end d-flex flex-column align-items-sm-end">
            <div class="mb-4">
                <div class="text-label mb-1"><?= trans("date"); ?></div>
                <div class="fw-bold text-dark"><?= $issueDate; ?></div>
            </div>

            <div class="mb-4">
                <div class="text-label mb-1"><?= trans("payment_method"); ?></div>
                <div class="fw-bold text-dark text-capitalize"><?= trans($transaction->payment_method, true); ?></div>
            </div>

            <div>
                <div class="text-label mb-1"><?= trans("status"); ?></div>
                <?php if ($transaction->status === 'succeeded'): ?>
                    <span class="badge rounded-pill px-3 py-1 mt-1 fw-bold" style="background-color: #e6f4ea; color: #1e8e3e; border: 1px solid #cce5d3; letter-spacing: 1.5px; font-size: 0.75rem;">
                        <?= trans("paid"); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="table-responsive mb-5">
        <table class="table table-borderless align-middle">
            <thead>
            <tr>
                <th class="ps-0" style="width: 75%;"><?= trans("description"); ?></th>
                <th class="text-end pe-0"><?= trans("amount"); ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="ps-0 py-3">
                    <div class="fw-bold text-dark fs-6 mb-1">
                        <?= !empty($order->item_id) ? '#' . esc($order->item_id) . ' - ' : ''; ?><?= esc($order->item_title); ?>
                    </div>

                    <div class="text-muted-custom fs-7 text-capitalize d-flex align-items-center gap-2">
                        <?php $orderType = $order->item_type ?? ''; ?>
                        <span><?= trans($orderType, true); ?></span>

                        <?php if (!empty($order->billing_cycle) && $orderType === 'subscription'): ?>
                            <span style="font-size: 10px;">&bull;</span>
                            <span><?= esc($order->billing_cycle); ?></span>
                        <?php endif; ?>
                    </div>
                </td>
                <td class="text-end pe-0 py-3 fw-bold fs-6 text-dark">
                    <?= esc($order->subtotal) . ' ' . $currency; ?>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="row justify-content-end mb-5">
        <div class="col-sm-6 col-md-5">
            <div class="d-flex justify-content-between mb-2 fs-7">
                <span class="text-muted-custom"><?= trans("subtotal"); ?></span>
                <span class="fw-semibold text-dark"><?= esc($order->subtotal ?? $order->total_amount) . ' ' . $currency; ?></span>
            </div>

            <?php if (!empty($order->tax) && $order->tax > 0): ?>
                <div class="d-flex justify-content-between mb-2 fs-7">
                    <span class="text-muted-custom"><?= trans("tax"); ?> (<?= esc($order->tax_rate); ?>%)</span>
                    <span class="fw-semibold text-dark"><?= esc($order->tax) . ' ' . $currency; ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($order->transaction_fee) && $order->transaction_fee > 0): ?>
                <div class="d-flex justify-content-between mb-3 fs-7">
                    <span class="text-muted-custom"><?= trans("transaction_fee"); ?> (<?= esc($order->transaction_fee_rate); ?>%)</span>
                    <span class="fw-semibold text-dark"><?= esc($order->transaction_fee) . ' ' . $currency; ?></span>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center border-top-custom pt-3 mt-2">
                <span class="fw-bold text-dark fs-6"><?= trans("total_paid"); ?></span>
                <span class="fw-bolder fs-5"><?= esc($order->total_amount) . ' ' . $currency; ?></span>
            </div>
        </div>
    </div>

    <?php if (!empty($order->order_token)): ?>
        <div class="row mt-4 mb-2">
            <div class="col-12" style="font-size: 13px; text-align: center">
                <span class="text-muted-custom"><?= trans("transaction_id"); ?> / <?= trans("reference"); ?></span>:&nbsp;#<?= esc($order->order_token); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="border-top-custom pt-4">
        <div class="row">
            <div class="col-sm-12 text-center text-muted-custom" style="font-size: 13px;">
                <?= esc($invoiceDetails->invoice_footer_note ?? ''); ?>
            </div>
        </div>
    </div>
</div>

<div class="container text-center mt-4 no-print">
    <div class="d-flex justify-content-center">
        <button onclick="window.print()" class="btn btn-dark px-5 py-2 fw-semibold shadow-sm d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-printer me-2 mb-1" viewBox="0 0 16 16">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
            </svg>
            <?= trans("print"); ?>
        </button>
    </div>
</div>

</body>
</html>