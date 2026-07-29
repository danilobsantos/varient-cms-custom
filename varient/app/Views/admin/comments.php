<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <div class="layout-content">

        <div class="card">
            <form action="<?= adminUrl('comments'); ?>" method="get" class="form-filter">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <?= view('admin/includes/_filter_rows'); ?>
                    </div>

                    <div class="w-100 mw-150px">
                        <select name="status" class="form-select form-select-solid select-comment-status" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("status", "attr"); ?>" data-kt-ecommerce-product-filter="status">
                            <option></option>
                            <option value="all" <?= inputGet('status') == 'all' ? 'selected' : ''; ?>><?= trans("all"); ?></option>
                            <option value="approved" <?= inputGet('status') == 'approved' ? 'selected' : ''; ?>><?= trans("approved"); ?></option>
                            <option value="pending" <?= inputGet('status') == 'pending' ? 'selected' : ''; ?>><?= trans("pending"); ?></option>
                        </select>
                    </div>
                </div>
            </form>

            <div class="card-body pt-5">
                <div class="table-responsive">

                    <table id="tableComments" class="table align-middle table-row-dashed table-bulk fs-6 gy-5">
                        <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-10px pe-2">
                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                    <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#tableComments .form-check-input" value="1"/>
                                </div>
                            </th>
                            <th class="min-w-20px"><?= trans('id'); ?></th>
                            <th class="min-w-200px"><?= trans('user'); ?></th>
                            <th><?= trans('comment'); ?></th>
                            <th class="min-w-100px"><?= trans('date'); ?></th>
                            <th class="text-end min-w-70px"><?= trans('options'); ?></th>
                        </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                        <?php if (!empty($comments)):
                            foreach ($comments as $item):
                                $post = (object)[
                                        'lang_id'  => $item->post_lang_id,
                                        'title'    => $item->post_title,
                                        'slug'     => $item->post_slug,
                                        'post_url' => $item->post_url,
                                ];
                                ?>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="<?= esc($item->id); ?>" data-status="<?= esc($item->status); ?>"/>
                                        </div>
                                    </td>

                                    <td><?= esc($item->id); ?></td>

                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-bold text-gray-800 fs-6"><?= esc($item->name); ?></span>
                                            </div>
                                            <span class="text-muted fs-7"><?= esc($item->email); ?></span>
                                            <span class="text-muted fs-8"><?= trans("ip_address"); ?>: <?= esc($item->ip_address); ?></span>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex flex-column">
                                            <div class="text-gray-900 mb-3 fs-6" style="word-break: break-word;">
                                                <?= esc($item->comment); ?>
                                            </div>

                                            <?php if (!empty($post)):
                                                $baseURL = generateBaseURLByLangId($post->lang_id); ?>
                                                <div class="text-muted fs-8 bg-light rounded px-3 py-2 align-self-start">
                                                    <?= trans('post'); ?>:
                                                    <a href="<?= generatePostUrl($post, $baseURL); ?>" target="_blank" class="fw-semibold text-gray-700 text-hover-primary">
                                                        <?= esc($post->title); ?>
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-muted fs-8 align-self-start">
                                                    <span class="badge badge-light-secondary"><?= trans('post_deleted'); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex flex-column gap-2 align-items-start">
                                            <span class="text-gray-800 fs-7"><?= formatDate($item->created_at); ?></span>

                                            <?php if ($item->status == 1): ?>
                                                <span class="badge badge-light-success badge-sm px-2 py-1"><?= trans('approved'); ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-light-danger badge-sm px-2 py-1"><?= trans('pending'); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td class="text-end">
                                        <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                            <?= trans('select'); ?><i class="ki-duotone ki-down fs-5 ms-1"></i>
                                        </a>
                                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 menu-table-options py-4" data-kt-menu="true">

                                            <?php if ($item->status != 1): ?>
                                                <div class="menu-item px-3">
                                                    <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                                       data-url="<?= adminUrl('comments'); ?>"
                                                       data-action="approve"
                                                       data-id="<?= $item->id; ?>"
                                                       data-message="<?= trans("msg_bulk_delete", "attr"); ?>">
                                                        <?= trans('approve'); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>

                                            <div class="menu-item px-3">
                                                <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                                   data-url="<?= adminUrl('comments'); ?>"
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

                    <div id="toolbarBulkActions" class="d-flex align-items-center d-none gap-2" data-kt-check-pending="true" data-kt-check-pending-target="#btnBulkApprove">
                        <button type="button" class="btn btn-sm btn-danger js-action-trigger"
                                data-url="<?= adminUrl('comments'); ?>"
                                data-action="delete"
                                data-message="<?= trans("msg_bulk_delete", "attr"); ?>"
                                data-confirm="1">
                            <?= trans("delete_selected"); ?>
                        </button>

                        <button id="btnBulkApprove" type="button" class="btn btn-sm btn-success js-action-trigger"
                                data-url="<?= adminUrl('comments'); ?>"
                                data-action="approve"
                                data-message="<?= trans("msg_bulk_approve", "attr"); ?>"
                                data-confirm="1">
                            <?= trans("approve"); ?>
                        </button>
                    </div>

                </div>

                <?php if (empty($comments)): ?>
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
    </div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
    <script>
        $(document).ready(function () {
            $('.select-comment-status').on('change', function () {
                $(this).closest('form').submit();
            });
        });
    </script>
<?= $this->endSection(); ?>