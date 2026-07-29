<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <div class="card">
        <form action="<?= $filterUrl; ?>" method="get" class="form-filter">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <h2><?= esc($language->name); ?></h2>
                </div>

                <div class="card-toolbar">
                    <div class="d-flex justify-content-end" data-kt-subscription-table-toolbar="base">
                        <?= view('admin/includes/_filters', [
                            'filterPageUrl' => $filterUrl,
                            'filters' => [
                                'search' => trans("search")
                            ]
                        ]); ?>
                    </div>
                </div>
            </div>
        </form>

        <div class="card-body pt-5">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-125px"><?= trans('label'); ?></th>
                        <th class="min-w-125px"><?= trans('translation'); ?></th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    <?php if (!empty($translations)):
                        foreach ($translations as $item): ?>
                            <tr>
                                <td class="w-40 min-w-125px" <?= (string)$language->text_direction === 'rtl' ? 'dir="rtl"' : ''; ?>>
                                    <span class="text-gray-600"><?= esc($item->label); ?></span>
                                </td>
                                <td class="w-60 min-w-300px" <?= (string)$language->text_direction === 'rtl' ? 'dir="rtl"' : ''; ?>>
                                    <input type="text" class="form-control input-language-translation" data-id="<?= esc($item->id); ?>" value="<?= esc($item->translation); ?>" <?= (string)$language->text_direction === 'rtl' ? 'dir="rtl"' : ''; ?>>
                                </td>
                            </tr>
                        <?php endforeach;
                    endif; ?>

                    </tbody>
                </table>
            </div>

            <?php if (empty($translations)): ?>
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