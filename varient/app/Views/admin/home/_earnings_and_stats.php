<?php
$userRewardEnabled = (int)user()->reward_system === 1;
$systemRewardEnabled = (int)$config->reward_system_status === 1;

$showEarnings = isAdmin() || ($userRewardEnabled && $systemRewardEnabled);
?>
    <div class="card card-flush overflow-hidden h-md-100">
        <div class="card-header py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold text-dark"><?= $showEarnings ? trans("earnings_and_stats") : trans("unique_pageviews"); ?></span>
                <span class="text-gray-500 mt-1 fw-semibold fs-6"><?= replaceMonthName(date("M Y")); ?></span>
            </h3>

            <?php if ($showEarnings): ?>
                <div class="card-toolbar">
                    <div class="dropdown" style="width: 250px;">
                        <button class="btn btn-light btn-sm dropdown-toggle fw-bold text-muted w-100 d-flex justify-content-between align-items-center"
                                type="button" id="chartDropdownMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <span id="chartSelectedText" class="text-truncate active fw-bold text-gray-800 fs-6">
                                <?= trans("unique_pageviews"); ?>
                            </span>
                        </button>

                        <ul class="dropdown-menu w-100 shadow-sm" aria-labelledby="chartDropdownMenu">
                            <li>
                                <a class="dropdown-item chart-tab-trigger active fw-bold text-gray-600"
                                   href="#kt_tab_pageviews">
                                    <?= trans("unique_pageviews"); ?>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item chart-tab-trigger fw-bold text-gray-600"
                                   href="#kt_tab_earnings">
                                    <?= isAdmin() ? trans("author_earnings") : trans("earnings"); ?>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item fw-bold text-gray-600" href="#" data-bs-toggle="modal"
                                   data-bs-target="#modalEarningsReport">
                                    <?= trans("detailed_report"); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card-body pt-0 pb-5 px-0">
            <div class="tab-content">

                <div class="tab-pane fade show active" id="kt_tab_pageviews">
                    <div class="d-flex flex-column px-9">
                        <div class="d-flex gap-1 mb-2 fs-2hx fw-bold text-gray-800 me-2 lh-1">
                            <?= numberFormatShort($monthlyStatsForChart->total_pageviews); ?>
                        </div>
                        <span class="fs-6 fw-semibold text-gray-500 mb-1"><?= trans("total_pageviews"); ?></span>

                        <div id="chartPageviews" class="min-h-auto ps-4 pe-6"
                             style="height: 320px; padding: 0 !important;"></div>
                    </div>
                </div>

                <?php if ($showEarnings): ?>
                    <div class="tab-pane fade" id="kt_tab_earnings">
                        <div class="d-flex flex-column px-9">
                            <div class="d-flex gap-1 mb-2 fs-2hx fw-bold text-gray-800 me-2 lh-1 price-format-chart">
                                <?= priceFormatted($monthlyStatsForChart->total_earnings); ?>
                            </div>
                            <span class="fs-6 fw-semibold text-gray-500 mb-1"><?= trans("total_earnings"); ?></span>

                            <div id="chartEarnings" class="min-h-auto ps-4 pe-6"
                                 style="height: 320px; padding: 0 !important;"></div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <style>
        /* Chart Bottom-to-Top Animation */
        @keyframes chartSlideUp {
            0% {
                opacity: 0;
                transform: translateY(40px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #chartPageviews,
        #chartEarnings {
            opacity: 0;
            animation: chartSlideUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        }
    </style>

<?= $this->section('scripts'); ?>

    <script>
        var VrChartsConfig = {
            data: {
                labels: <?= json_encode($monthlyStatsForChart->labels); ?>,
                views: <?= json_encode($monthlyStatsForChart->views); ?>,
                earnings: <?= json_encode($monthlyStatsForChart->earnings); ?>,
                posts: {
                    active: <?= (int)$dashboardPostStats->active_post_count; ?>,
                    scheduled: <?= (int)$dashboardPostStats->scheduled_post_count; ?>,
                    pending: <?= (int)$dashboardPostStats->pending_post_count; ?>
                }
            },
            settings: {
                currencySymbol: '<?= esc($config->currency_settings->symbol); ?>'
            },
            texts: {
                pageviews: '<?= json_encode(trans("pageviews", "raw")); ?>',
                earnings: '<?= json_encode(trans("earnings", "raw")); ?>',
                active: '<?= json_encode(trans("active", "raw")); ?>',
                scheduled: '<?= json_encode(trans("scheduled", "raw")); ?>',
                pending: '<?= json_encode(trans("pending", "raw")); ?>'
            },
            charts: {
                showPageviews: true,
                showEarnings: true
            }
        };

        // Chart dropdown change
        document.addEventListener("DOMContentLoaded", function () {
            const dropdownLinks = document.querySelectorAll('.chart-tab-trigger');

            dropdownLinks.forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();

                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });

                    const target = document.querySelector(link.getAttribute('href'));
                    if (target) target.classList.add('show', 'active');

                    const dropdownText = document.getElementById('chartSelectedText');
                    if (dropdownText) dropdownText.textContent = link.textContent;

                    dropdownLinks.forEach(l => l.classList.remove('active'));
                    link.classList.add('active');
                });
            });
        });

    </script>

    <script src="<?= base_url('assets/admin/js/charts.js'); ?>"></script>
<?= $this->endSection(); ?>