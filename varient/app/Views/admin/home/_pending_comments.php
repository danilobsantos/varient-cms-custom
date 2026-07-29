<div class="card card-flush h-100">
    <div class="card-header pt-7">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold text-gray-900"><?= trans("pending_comments"); ?></span>
            <span class="text-gray-500 mt-1 fw-semibold fs-6"><?= trans("pending_comments_exp"); ?></span>
        </h3>

        <div class="card-toolbar">
            <div class="d-flex flex-stack flex-wrap gap-4">
                <a href="<?= adminUrl("comments"); ?>" class="btn btn-light btn-sm"><?= trans("view_all"); ?></a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="hover-scroll-overlay-y" style="max-height: 420px;">
            <table class="table align-middle table-row-dashed fs-6 gy-3">
                <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-20px"><?= trans('id'); ?></th>
                    <th class="min-w-125px"><?= trans('user'); ?></th>
                    <th class="min-w-125px"><?= trans('comment'); ?></th>
                    <th><?= trans('date'); ?></th>
                </tr>
                </thead>
                <tbody class="fw-bold text-gray-600">
                <?php if (!empty($latestPendingComments)):
                    foreach ($latestPendingComments as $item): ?>
                        <tr>
                            <td><?= esc($item->id); ?></td>
                            <td><?= esc($item->name); ?><br><?= esc($item->email); ?></td>
                            <td class="text-gray-900 fw-normal" style="width: 50%; word-break: break-word">
                                <?= esc(characterLimiter($item->comment, 125, '...')); ?>
                            </td>
                            <td><?= formatDate($item->created_at); ?></td>
                        </tr>
                    <?php endforeach;
                endif; ?>
                </tbody>
            </table>

            <?php if (empty($latestPendingComments)): ?>
                <div class="d-flex flex-column align-items-center justify-content-center h-100 py-10">
                    <span class="text-muted fw-semibold fs-6">
                        <?= trans("no_records_found"); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>