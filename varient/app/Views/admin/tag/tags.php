<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <div class="d-flex flex-column flex-xl-row g-5 g-xl-7 g-xxl-10 mb-5 mb-xl-7 mb-xxl-10">
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-xl-10">
            <div class="card">
                <form action="<?= adminUrl('tags'); ?>" method="get" class="form-filter">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <?= view('admin/includes/_filter_rows'); ?>
                        </div>

                        <div class="card-toolbar">
                            <div class="d-flex justify-content-end gap-3" data-kt-subscription-table-toolbar="base">
                                <?= view('admin/includes/_filters', [
                                        'filterPageUrl' => adminUrl('tags'),
                                        'filters'       => [
                                                'language',
                                                'search' => trans("tag")
                                        ]
                                ]); ?>

                                <button type="button" class="btn btn-primary btn-tag-modal-trigger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#tagFormModal"
                                        data-title="<?= trans("add_tag"); ?>"
                                        data-btn-text="<?= trans("add_tag"); ?>">
                                    <i class="ki-duotone ki-plus fs-2"></i>
                                    <?= trans("add_tag"); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="card-body pt-5">
                    <div class="table-responsive">
                        <table id="tableTags" class="table align-middle table-row-dashed table-bulk fs-6 gy-5">
                            <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="w-10px pe-2">
                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                        <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#tableTags .form-check-input" value="1"/>
                                    </div>
                                </th>
                                <th class="min-w-20px"><?= trans('id'); ?></th>
                                <th class="min-w-125px"><?= trans('tag'); ?></th>
                                <th class="min-w-125px"><?= trans('language'); ?></th>
                                <th class="min-w-125px"><?= trans('number_of_posts'); ?></th>
                                <th class="text-end min-w-70px"><?= trans('options'); ?></th>
                            </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                            <?php if (!empty($tags)):
                                foreach ($tags as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                <input class="form-check-input" type="checkbox" value="<?= esc($item->id); ?>"/>
                                            </div>
                                        </td>
                                        <td><?= esc($item->id); ?></td>
                                        <td>
                                            <span class="text-gray-900 mb-1"><?= esc($item->tag); ?></span>
                                        </td>
                                        <td><?= esc(getLanguageClient($item->lang_id)?->name ?? ''); ?></td>
                                        <td><?= esc($item->count); ?></td>
                                        <td class="text-end">
                                            <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                <?= trans('select'); ?><i class="ki-duotone ki-down fs-5 ms-2"></i>
                                            </a>
                                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 menu-table-options py-4" data-kt-menu="true">
                                                <div class="menu-item px-3">
                                                    <a href="javascript:void(0)" class="menu-link px-3 btn-tag-modal-trigger"
                                                       data-bs-toggle="modal"
                                                       data-bs-target="#tagFormModal"
                                                       data-title="<?= trans("update"); ?>"
                                                       data-btn-text="<?= trans("save_changes"); ?>"
                                                       data-id="<?= $item->id; ?>"
                                                       data-tag="<?= esc($item->tag); ?>"
                                                       data-lang-id="<?= $item->lang_id; ?>">
                                                        <?= trans("edit"); ?>
                                                    </a>
                                                </div>
                                                <div class="menu-item px-3">
                                                    <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                                       data-url="<?= base_url('Category/deleteTag'); ?>"
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

                        <div id="toolbarBulkActions" class="d-flex align-items-center d-none gap-2" data-kt-check-pending="true">
                            <button type="button" class="btn btn-sm btn-danger js-action-trigger"
                                    data-url="<?= base_url('Category/deleteTag'); ?>"
                                    data-action="delete"
                                    data-message="<?= trans("msg_bulk_delete", "attr"); ?>"
                                    data-confirm="1">
                                <?= trans("delete_selected"); ?>
                            </button>
                        </div>

                    </div>

                    <?php if (empty($tags)): ?>
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
    </div>

<?= view("admin/tag/_form_modal"); ?>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
    <script>
        $(document).on('click', '.btn-tag-modal-trigger', function () {
            var modal = $('#tagFormModal');
            var form = modal.find('form.kt-form');
            var button = $(this);

            var title = button.data('title');
            var btnText = button.data('btn-text');
            var id = button.data('id') || '';
            var tagName = button.data('tag') || '';
            var langId = button.data('lang-id') || null;

            modal.find('.modal-title').text(title);
            modal.find('.indicator-label').text(btnText);
            modal.find('input[name="id"]').val(id);
            modal.find('input[name="tag"]').val(tagName);

            if (langId) {
                var langSelect = modal.find('select[name="lang_id"]');
                langSelect.val(langId);
                langSelect.trigger('change.select2');
            }
        });
    </script>
<?= $this->endSection(); ?>