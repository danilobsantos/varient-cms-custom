<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$postActionUrl = base_url("Post/postAction");

$bulkActions = [
        'add_slider',
        'add_featured',
        'add_breaking_news',
        'add_recommended',
        'divider',
        'remove_slider',
        'remove_featured',
        'remove_breaking_news',
        'remove_recommended',
];
?>

    <div class="card">
        <form action="<?= $action; ?>" method="get" class="form-filter">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <?= view('admin/includes/_filter_rows'); ?>
                </div>

                <div class="card-toolbar">
                    <div class="d-flex justify-content-end gap-3" data-kt-subscription-table-toolbar="base">
                        <?= view('admin/post/_filters'); ?>

                        <a href="<?= adminUrl('add-post/format'); ?>" class="btn btn-primary">
                            <i class="ki-duotone ki-plus fs-2"></i>
                            <?= trans("add_post"); ?>
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <div class="card-body pt-5">
            <div class="table-responsive">
                <table id="tablePosts" class="table align-middle table-row-dashed table-bulk fs-6 gy-5">
                    <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <?php if (hasPermission('manage_all_posts')): ?>
                            <th class="w-10px pe-2">
                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                    <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#tablePosts .form-check-input" value="1"/>
                                </div>
                            </th>
                        <?php endif; ?>
                        <th><?= trans('id'); ?></th>
                        <th class="min-w-125px"><?= trans('post'); ?></th>
                        <th class="min-w-125px"><?= trans('post_format'); ?></th>
                        <th><?= trans('pageviews'); ?></th>
                        <th><?= trans('date'); ?></th>
                        <th class="text-end min-w-70px"><?= trans('options'); ?></th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    <?php if (!empty($posts)):
                        foreach ($posts as $item):
                            $language = getLanguageClient($item->lang_id, $config);
                            $postSelections = !empty($item->selection_types) ? explode(',', $item->selection_types) : [];

                            $postUrl = '#';
                            if ((int)$item->status !== 1 || (int)$item->visibility !== 1 || (int)$item->is_scheduled === 1) {
                                $postUrl = generateBaseURLByLang($language) . 'post/preview/' . $item->slug;
                            } else {
                                $postUrl = generateBaseURLByLang($language) . $item->slug;
                            }
                            ?>
                            <tr>
                                <?php if (hasPermission('manage_all_posts')): ?>
                                    <td>
                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="<?= esc($item->id); ?>"/>
                                        </div>
                                    </td>
                                <?php endif; ?>

                                <td class="w-60px"><?= esc($item->id); ?></td>

                                <td class="d-flex">
                                    <div class="symbol symbol-75px overflow-hidden me-3">
                                        <a href="<?= esc($postUrl); ?>" target="_blank" class="<?= $item->status != 1 ? 'disabled-link' : ''; ?>">
                                            <div class="symbol-label">
                                                <img src="<?= getPostImageUrl($item, "small"); ?>" alt="" class="w-75px h-75px object-fit-cover" loading="lazy"/>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <a href="<?= esc($postUrl); ?>" class="text-gray-800 text-hover-primary mb-1<?= $item->status != 1 ? ' disabled-link' : ''; ?>" target="_blank">
                                            <?= esc($item->title); ?>
                                        </a>

                                        <div class="text-muted fs-7 mb-1">
                                            <a href="<?= generateProfileURL($item->author_slug); ?>" target="_blank" class="fw-semibold text-gray-700 text-hover-primary">
                                                <?= esc($item->author_username); ?>
                                            </a>
                                            <span class="mx-1">•</span>
                                            <?= esc($item->category_name); ?>
                                            <span class="mx-1">•</span>
                                            <?= !empty($language) ? esc($language->name) : ''; ?>
                                        </div>

                                        <div class="d-flex flex-wrap align-items-center gap-1 mt-1">

                                            <?php if ($premiumMembership->status): ?>
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php if ($premiumMembership->exclusiveSaleStatus && (int)$item->is_exclusive === 1): ?>
                                                        <span class="badge badge-success"><?= trans('exclusive'); ?></span>
                                                    <?php else:
                                                        if ($premiumMembership->subscriptionMode === 'selective' && (int)$item->is_premium === 1): ?>
                                                            <span class="badge badge-primary"><?= trans('premium'); ?></span>
                                                        <?php endif;
                                                    endif; ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php $displayConfig = [
                                                    'slider'        => 'primary',
                                                    'featured'      => 'success',
                                                    'recommended'   => 'info',
                                                    'breaking_news' => 'warning'
                                            ];

                                            $orderableTypes = ['slider', 'featured']; ?>

                                            <?php foreach ($displayConfig as $type => $color):
                                                if (in_array($type, $postSelections)):
                                                    if (hasPermission('manage_all_posts') && in_array($type, $orderableTypes)): ?>
                                                        <div class="d-inline-flex align-items-center me-2 badge-input-wrap">
                                                            <span class="badge badge-light-<?= $color; ?> px-3 py-2">
                                                                <?= trans($type); ?>
                                                            </span>
                                                            <input type="number" class="order-input js-order-update" value="<?= $item->{$type . '_order'} ?? '' ?>" min="1" placeholder="#" data-id="<?= $item->id ?>" data-type="<?= $type ?>">
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="badge badge-light-<?= $color; ?> px-4 py-2 me-1">
                                                            <?= trans($type); ?>
                                                        </span>
                                                    <?php endif;
                                                endif;
                                            endforeach; ?>

                                            <?php if (!empty($item->need_auth)): ?>
                                                <span class="badge badge-light-danger px-4 py-2">
                                                    <?= trans('only_registered'); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="mt-1">
                                            <?php if ((int)$item->is_scheduled === 1):
                                                if ((int)$item->visibility !== 1): ?>
                                                    <span class="badge badge-light-primary"><?= trans('scheduled'); ?></span>
                                                <?php endif; ?>
                                                <span class="badge badge-light-primary"><?= trans('days_remaining'); ?>:&nbsp;<strong><?= dateDifference(date('Y-m-d'), $item->scheduled_at); ?></strong></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= trans($item->post_format); ?></td>
                                <td><?= numberFormatShort($item->pageviews); ?></td>
                                <td>
                                    <?= $item->created_at; ?>
                                    <?php if (!empty($item->updated_at) && $item->created_at != $item->updated_at): ?>
                                        <div class="text-muted m-t-5">
                                            <small class="font-600"><?= trans("edited"); ?>:&nbsp;<?= timeAgo($item->updated_at); ?></small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        <?= trans('select'); ?><i class="ki-duotone ki-down fs-5 ms-2"></i>
                                    </a>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 menu-table-options py-4" data-kt-menu="true">

                                        <?php if ($item->post_format == 'event'): ?>
                                            <div class="menu-item px-3">
                                                <a href="<?= adminUrl('events/event/' . esc($item->id)); ?>" class="menu-link px-3"><?= trans('details'); ?></a>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($listType) && in_array($listType, ['drafts'])): ?>
                                            <div class="menu-item px-3">
                                                <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                                   data-url="<?= $postActionUrl; ?>"
                                                   data-action="publish_draft"
                                                   data-id="<?= $item->id; ?>"
                                                   data-message="<?= trans("confirm_action", "attr"); ?>"
                                                   data-confirm="1">
                                                    <?= trans('publish'); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($listType) && in_array($listType, ['scheduled_posts'])): ?>
                                            <div class="menu-item px-3">
                                                <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                                   data-url="<?= $postActionUrl; ?>"
                                                   data-action="publish_scheduled"
                                                   data-id="<?= $item->id; ?>"
                                                   data-message="<?= trans("confirm_action", "attr"); ?>"
                                                   data-confirm="1">
                                                    <?= trans('publish'); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (hasPermission('manage_all_posts') && !empty($listType) && in_array($listType, ['pending_posts'])): ?>
                                            <div class="menu-item px-3">
                                                <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                                   data-url="<?= $postActionUrl; ?>"
                                                   data-action="approve"
                                                   data-id="<?= $item->id; ?>"
                                                   data-message="<?= trans("confirm_action", "attr"); ?>"
                                                   data-confirm="1">
                                                    <?= trans('approve'); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>

                                        <div class="menu-item px-3">
                                            <a href="<?= adminUrl('posts/edit/' . esc($item->id)); ?>" class="menu-link px-3"><?= trans('edit'); ?></a>
                                        </div>

                                        <?php
                                        if (hasPermission('manage_all_posts') && !empty($listType) && !in_array($listType, ['drafts', 'pending_posts', 'scheduled_posts'])):
                                            $selectionTypes = ['slider', 'featured', 'recommended', 'breaking_news'];

                                            foreach ($selectionTypes as $type):
                                                $isActive = in_array($type, $postSelections);
                                                $action = $isActive ? 'remove_' . $type : 'add_' . $type;
                                                ?>
                                                <div class="menu-item px-3">
                                                    <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                                       data-url="<?= $postActionUrl; ?>"
                                                       data-action="<?= $action; ?>"
                                                       data-id="<?= $item->id; ?>">
                                                        <?= trans($action); ?>
                                                    </a>
                                                </div>
                                            <?php endforeach;
                                        endif; ?>

                                        <div class="menu-item px-3">
                                            <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                               data-url="<?= $postActionUrl; ?>"
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

            <?php if (empty($posts)): ?>
                <p class="text-muted text-center mt-6">
                    <?= trans("no_records_found"); ?>
                </p>
            <?php endif; ?>

            <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
                <div class="d-flex justify-content-end align-items-center mt-5">
                    <?= $pager->links(); ?>
                </div>
            <?php endif; ?>

            <div id="toolbarBulkActions" class="d-flex align-items-center d-none">
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle disabled-after gap-1 min-w-125px" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= trans("bulk_actions") ?>
                        <i class="ki-duotone ki-down m-0 p-0 fs-2"></i>
                    </button>
                    <ul class="dropdown-menu px-3 min-w-125px">

                        <?php if (!empty($listType) && !in_array($listType, ['drafts', 'pending_posts', 'scheduled_posts'])): ?>

                            <?php foreach ($bulkActions as $action):
                                if ($action === 'divider'): ?>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php else: ?>
                                    <li>
                                        <button type="button"
                                                class="dropdown-item menu-gray-800 fw-semibold js-action-trigger"
                                                data-url="<?= $postActionUrl; ?>"
                                                data-action="<?= $action; ?>"
                                                data-message="<?= trans("confirm_action", "attr"); ?>"
                                                data-confirm="1">
                                            <?= trans($action); ?>
                                        </button>
                                    </li>
                                <?php endif;
                            endforeach; ?>

                            <li>
                                <hr class="dropdown-divider">
                            </li>
                        <?php endif; ?>

                        <?php if (!empty($listType) && in_array($listType, ['drafts'])): ?>
                            <li>
                                <button type="button"
                                        class="dropdown-item menu-gray-800 fw-semibold js-action-trigger"
                                        data-url="<?= $postActionUrl; ?>"
                                        data-action="publish_draft"
                                        data-message="<?= trans("confirm_action", "attr"); ?>"
                                        data-confirm="1">
                                    <?= trans("publish"); ?>
                                </button>
                            </li>
                        <?php endif; ?>

                        <?php if (!empty($listType) && in_array($listType, ['scheduled_posts'])): ?>
                            <li>
                                <button type="button"
                                        class="dropdown-item menu-gray-800 fw-semibold js-action-trigger"
                                        data-url="<?= $postActionUrl; ?>"
                                        data-action="publish_scheduled"
                                        data-message="<?= trans("confirm_action", "attr"); ?>"
                                        data-confirm="1">
                                    <?= trans("publish"); ?>
                                </button>
                            </li>
                        <?php endif; ?>

                        <?php if (!empty($listType) && in_array($listType, ['pending_posts'])): ?>
                            <li>
                                <button type="button"
                                        class="dropdown-item menu-gray-800 fw-semibold js-action-trigger"
                                        data-url="<?= $postActionUrl; ?>"
                                        data-action="approve"
                                        data-message="<?= trans("confirm_action", "attr"); ?>"
                                        data-confirm="1">
                                    <?= trans("approve"); ?>
                                </button>
                            </li>
                        <?php endif; ?>

                        <li>
                            <button type="button"
                                    class="dropdown-item text-danger fw-semibold js-action-trigger"
                                    data-url="<?= $postActionUrl; ?>"
                                    data-action="delete"
                                    data-message="<?= trans("msg_bulk_delete", "attr"); ?>"
                                    data-confirm="1">
                                <?= trans("delete"); ?>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
    <script>
        $(document).ready(function () {
            // Updates post order (Slider / Featured)
            $(document).on('change', '.js-order-update', function () {
                let $input = $(this);
                let $wrapper = $input.closest('.badge-input-wrap');

                let payload = {
                    id: $input.data('id'),
                    type: $input.data('type'),
                    order: $input.val()
                };

                $wrapper.addClass('status-loading');

                $.ajax({
                    url: generateUrl('Post/updatePostOrder'),
                    type: 'POST',
                    data: payload,
                    dataType: 'json',
                    success: function (response) {
                        $wrapper.removeClass('status-loading').addClass('status-success');

                        setTimeout(function () {
                            $wrapper.removeClass('status-success');
                        }, 500);
                    },
                    error: function (xhr, status, error) {
                        $wrapper.removeClass('status-loading').addClass('status-error');

                        setTimeout(function () {
                            $wrapper.removeClass('status-error');
                        }, 500);
                    }
                });
            });
        });
    </script>
<?= $this->endSection(); ?>