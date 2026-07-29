<div class="modal fade" id="modalEarningsReport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold"><?= trans("detailed_report"); ?></h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>

            <div class="modal-body py-10 px-lg-17">
                <div class="table-responsive">
                    <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                        <thead>
                        <tr class="fs-7 fw-bold text-gray-400 border-bottom-0">
                            <th class="p-0 pb-3 min-w-150px"><?= trans('date'); ?></th>
                            <th class="p-0 pb-3 min-w-100px"><?= trans('pageviews'); ?></th>
                            <th class="p-0 pb-3 min-w-100px text-end"><?= trans('earnings'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($monthlyStatsForChart) && !empty($monthlyStatsForChart->labels)):
                            $today = date('Y-m-d');
                            foreach ($monthlyStatsForChart->labels as $index => $date):
                                if ($date > $today) {
                                    continue;
                                }
                                $viewCount = $monthlyStatsForChart->views[$index] ?? 0;
                                $earningAmount = $monthlyStatsForChart->earnings[$index] ?? 0;
                                ?>
                                <tr>
                                    <td>
                                        <span class="text-gray-600 fw-semibold fs-6">
                                            <?= replaceMonthName(date("M j, Y", strtotime($date))); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <span class="text-gray-800 fw-bold fs-6">
                                            <?= number_format($viewCount); ?>
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <span class="text-success fw-bold fs-6">
                                            <?= priceFormatted($earningAmount, getRewardPriceDecimal()); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php
                            endforeach;
                        endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal"><?= trans("close"); ?></button>
            </div>

        </div>
    </div>
</div>