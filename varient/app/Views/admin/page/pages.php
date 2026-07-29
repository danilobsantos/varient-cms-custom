<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <div class="card">
        <form action="<?= adminUrl('pages'); ?>" method="get" class="form-filter">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <?= view('admin/includes/_filter_rows'); ?>
                </div>

                <div class="card-toolbar">
                    <div class="d-flex justify-content-end gap-3" data-kt-subscription-table-toolbar="base">
                        <?= view('admin/includes/_filters', [
                                'filterPageUrl' => adminUrl('pages'),
                                'filters'       => [
                                        'language',
                                        'status',
                                        'search' => trans("title")
                                ]
                        ]); ?>

                        <a href="<?= adminUrl('pages/add'); ?>" class="btn btn-primary">
                            <i class="ki-duotone ki-plus fs-2"></i>
                            <?= trans("add_page"); ?>
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
                        <th class="min-w-125px"><?= trans('title'); ?></th>
                        <th class="min-w-125px"><?= trans('status'); ?></th>
                        <th class="min-w-125px"><?= trans('page_type'); ?></th>
                        <th class="min-w-125px"><?= trans('date_added'); ?></th>
                        <th class="text-end min-w-70px"><?= trans('options'); ?></th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">

                    <?php if (!empty($pages)):
                        foreach ($pages as $item): ?>
                            <tr>
                                <td><?= esc($item->id); ?></td>
                                <td>
                                    <div class="text-gray-900 mb-1"><?= esc($item->title); ?></div>

                                    <div class="text-muted fs-7">
                                        <?= match (esc($item->location)) {
                                            'top' => trans('top_menu'),
                                            'main' => trans('main_menu'),
                                            'footer' => trans('footer'),
                                            default => '-',
                                        } ?>
                                        <span class="mx-1">•</span>
                                        <?= esc($item->language_name); ?>
                                    </div>
                                </td>

                                <td>
                                    <?php if ($item->status == 1): ?>
                                        <span class="badge badge-light-success me-auto px-4 py-2"><?= trans('active'); ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-light-danger me-auto px-4 py-2"><?= trans('inactive'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($item->is_custom == 1): ?>
                                        <span class="badge badge-light-primary me-auto px-4 py-2"><?= trans('custom'); ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-light me-auto px-4 py-2"><?= trans('default'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= formatDate($item->created_at); ?></td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        <?= trans('select'); ?><i class="ki-duotone ki-down fs-5 ms-2"></i>
                                    </a>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 menu-table-options py-4" data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <a href="<?= adminUrl('pages/edit/' . esc($item->id)); ?>" class="menu-link px-3"><?= trans('edit'); ?></a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                               data-url="<?= base_url('Admin/deletePage'); ?>"
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
                        <?php endforeach;
                    endif; ?>

                    </tbody>
                </table>
            </div>

            <?php if (empty($pages)): ?>
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