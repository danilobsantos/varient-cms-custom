<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<div class="card">
    <form action="<?= adminUrl('polls'); ?>" method="get" class="form-filter">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <?= view('admin/includes/_filter_rows'); ?>
            </div>

            <div class="card-toolbar">
                <div class="d-flex justify-content-end gap-3" data-kt-subscription-table-toolbar="base">
                    <?= view('admin/includes/_filters', [
                            'filterPageUrl' => adminUrl('polls'),
                            'filters'       => [
                                    'language',
                                    'status',
                                    'search' => trans("question")
                            ]
                    ]); ?>

                    <a href="<?= adminUrl('polls/add'); ?>" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        <?= trans("add_poll"); ?>
                    </a>
                </div>
            </div>
        </div>
    </form>

    <div class="card-body pt-5">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-20px"><?= trans('id'); ?></th>
                    <th class="min-w-125px"><?= trans('question'); ?></th>
                    <th class="min-w-125px"><?= trans('language'); ?></th>
                    <th class="min-w-125px"><?= trans('vote_permission'); ?></th>
                    <th class="min-w-125px"><?= trans('status'); ?></th>
                    <th class="min-w-125px"><?= trans('date_added'); ?></th>
                    <th class="text-end min-w-70px"><?= trans('options'); ?></th>
                </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">

                <?php if (!empty($polls)):
                    foreach ($polls as $item): ?>
                        <tr>
                            <td><?= esc($item->id); ?></td>

                            <td>
                                <div class="text-gray-900 mb-1"><?= esc($item->question); ?></div>
                                <button type="button" class="btn btn-sm btn-light-info" data-bs-toggle="modal" data-bs-target="#kt_modal_<?= $item->id; ?>">
                                    <i class="ki-duotone ki-chart-simple-2 fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                    <?= trans('view_results'); ?>
                                </button>
                            </td>

                            <td><?= esc($item->language_name); ?></td>

                            <td><?= $item->vote_permission == "all" ? trans('all_users_can_vote') : trans('registered_users_can_vote'); ?></td>

                            <td>
                                <?php if ($item->status == 1): ?>
                                    <span class="badge badge-light-success me-auto px-4 py-2"><?= trans('active'); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-light-danger me-auto px-4 py-2"><?= trans('inactive'); ?></span>
                                <?php endif; ?>
                            </td>

                            <td><?= formatDate($item->created_at); ?></td>

                            <td class="text-end">
                                <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    <?= trans('select'); ?><i class="ki-duotone ki-down fs-5 ms-2"></i>
                                </a>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 menu-table-options py-4" data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <a href="<?= adminUrl('polls/edit/' . esc($item->id)); ?>" class="menu-link px-3"><?= trans('edit'); ?></a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                           data-url="<?= base_url('Admin/deletePoll'); ?>"
                                           data-action="delete"
                                           data-id="<?= $item->id; ?>"
                                           data-message="<?= trans("confirm_delete", "attr"); ?>"
                                           data-confirm="1">
                                            <?= trans('delete'); ?>
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" tabindex="-1" id="kt_modal_<?= $item->id; ?>">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h3 class="modal-title"><?= trans("results"); ?></h3>
                                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                                        </div>
                                    </div>

                                    <div class="modal-body">
                                        <p class="fw-bold">
                                            <?= esc($item->question); ?>
                                        </p>

                                        <?php $options = model('PollOptionModel')->findAllByPollId($item->id);
                                        if (!empty($options)):
                                            $totalVotes = array_sum(array_column($options, 'votes'));

                                            foreach ($options as $option):
                                                $voteCount = $option->votes;
                                                $percent = ($totalVotes > 0) ? round(($voteCount / $totalVotes) * 100) : 0; ?>
                                                <p class="mb-1 mt-8"><?= esc($option->option_text); ?> (<?= $voteCount; ?>)</p>
                                                <div class="progress" role="progressbar" aria-label="Poll option" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100" style="height: 20px; font-size: 12px; font-weight: 600">
                                                    <div class="progress-bar text-bg-success" style="width: <?= $percent; ?>%">
                                                        <?= $percent; ?>%
                                                    </div>
                                                </div>
                                            <?php endforeach;
                                        endif; ?>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= trans("close"); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endforeach;
                endif; ?>

                </tbody>
            </table>
        </div>

        <?php if (empty($polls)): ?>
            <p class="text-muted text-center mt-6">
                <?= trans("no_records_found"); ?>
            </p>
        <?php endif; ?>

        <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
            <div class="d-flex justify-content-end align-items-center mt-5">
                <?= $pager->links(); ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?= $this->endSection(); ?>
