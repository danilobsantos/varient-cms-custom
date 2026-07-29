<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$eventData = $event->post_data ?? null;
$eventDetails = $eventData->details ?? null;

// Basic Info
$eventLocation = $eventDetails->venue_name ?? '--';
$eventAddress = $eventDetails->map_address ?? $eventDetails->location ?? '--';
$organizer = $eventDetails->organizer ?? '--';
$eventSummary = $event->summary ?? '';
$eventContent = $event->content ?? '';

// Dates & Times
$rawStartDate = $event->event_start_date ?? null;
$rawEndDate = $event->event_end_date ?? null;
$startDate = $rawStartDate ? formatDateClient(date('Y-m-d', strtotime($rawStartDate))) . ' <span class="text-gray-600 ms-1">' . date('H:i', strtotime($rawStartDate)) . '</span>' : '--';
$endDate = $rawEndDate ? formatDateClient(date('Y-m-d', strtotime($rawEndDate))) . ' <span class="text-gray-600 ms-1">' . date('H:i', strtotime($rawEndDate)) . '</span>' : '--';
echo $eventDetails->registration_type;

// Registration Info
$eventCapacity = !empty($eventDetails->registration_capacity) ? (int)$eventDetails->registration_capacity : 0;
$regTypeText = trans("no_registration_required");
if ($eventDetails->registration_type === 'external') {
    $regTypeText = trans("event_external_link_label");
} else if ($eventDetails->registration_type === 'internal') {
    $regTypeText = trans("internal_registration");
}

$regDeadline = !empty($eventDetails->registration_deadline) ? formatDateClient(date('Y-m-d H:i', strtotime($eventDetails->registration_deadline))) : '--';
$regAccess = $eventDetails->registration_access === 'registered' ? trans("registered_users_only") : trans("public_open_to_everyone");

// Extra Lists
$scheduleList = $eventData->schedule ?? [];
$speakersList = $eventData->speakers ?? [];

// Participant Calculations
$participants = $participants ?? [];
$totalParticipants = isset($pager) ? $pager->getTotal() : count($participants);

$occupancyRate = 0;
$occupancyColor = 'bg-primary';
if ($eventCapacity > 0) {
    $occupancyRate = min(100, round(($totalParticipants / $eventCapacity) * 100));
    if ($occupancyRate >= 90) {
        $occupancyColor = 'bg-danger';
    } elseif ($occupancyRate >= 50) {
        $occupancyColor = 'bg-warning';
    } else {
        $occupancyColor = 'bg-success';
    }
}
?>

    <div class="card mb-6">
        <div class="card-body pt-9 pb-0">
            <div class="d-flex flex-wrap flex-sm-nowrap">

                <div class="me-7 mb-4">
                    <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative bg-light-primary d-flex justify-content-center align-items-center rounded-3 border border-primary border-dashed">
                        <i class="ki-duotone ki-calendar-8 fs-3x text-primary">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            <span class="path4"></span><span class="path5"></span><span class="path6"></span>
                        </i>
                    </div>
                </div>

                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">

                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center mb-1">
                                <span class="text-gray-900 fs-2 fw-bold me-2"><?= esc($event->title ?? trans('event')); ?></span>
                            </div>

                            <?php if (!empty($eventSummary)): ?>
                                <div class="mt-1 mb-3">
                                    <span class="text-gray-600 fs-6 fw-semibold d-block">
                                        <?= esc($eventSummary); ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2 gap-5">
                                <span class="d-flex align-items-center text-gray-700 mb-2">
                                <i class="ki-duotone ki-time fs-4 me-1 text-primary"><span class="path1"></span><span class="path2"></span></i>
                                <?= $startDate; ?>
                                    <?= $rawEndDate ? ' <span class="mx-2 text-gray-300">|</span> ' . $endDate : ''; ?>
                                </span>
                                <span class="d-flex align-items-center text-gray-500 mb-2">
                                    <i class="ki-duotone ki-geolocation fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                    <?= esc($eventLocation); ?>
                                </span>
                            </div>
                        </div>

                        <div class="d-flex mb-4 gap-2">
                            <a href="<?= generatePostUrl($event); ?>" class="btn btn-sm btn-light-success" target="_blank">
                                <i class="ki-duotone ki-exit-right-corner"><span class="path1"></span><span class="path2"></span></i>
                                <?= trans("view"); ?>
                            </a>
                            <a href="<?= adminUrl('posts/edit/' . esc($event->id)); ?>" class="btn btn-sm btn-light-primary">
                                <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                                <?= trans("edit"); ?>
                            </a>
                            <a href="javascript:void(0)" class="btn btn-sm btn-light-danger js-action-trigger"
                               data-url="<?= base_url("Post/postAction"); ?>"
                               data-action="delete"
                               data-id="<?= esc($event->id); ?>"
                               data-message="<?= trans("confirm_delete", "attr"); ?>"
                               data-confirm="1">
                                <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                <?= trans("delete"); ?>
                            </a>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap flex-stack">
                        <div class="d-flex flex-column flex-grow-1 pe-8">
                            <div class="d-flex flex-wrap">
                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-2 fw-bold text-gray-900"><?= number_format($totalParticipants); ?></div>
                                    </div>
                                    <div class="fw-semibold fs-6 text-gray-500"><?= trans("participants"); ?></div>
                                </div>

                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-2 fw-bold text-gray-900">
                                            <?= $eventCapacity > 0 ? number_format($eventCapacity) : '<span class="fs-4">∞</span>'; ?>
                                        </div>
                                    </div>
                                    <div class="fw-semibold fs-6 text-gray-500"><?= trans("capacity_limit"); ?></div>
                                </div>

                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-3 fw-bold text-gray-900 text-capitalize">
                                            <?= esc($regTypeText); ?>
                                        </div>
                                    </div>
                                    <div class="fw-semibold fs-6 text-gray-500"><?= trans("registration_type"); ?></div>
                                </div>
                            </div>
                        </div>

                        <?php if ($eventCapacity > 0): ?>
                            <div class="w-200px w-sm-300px d-flex flex-column mt-3 mb-3">
                                <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                                    <span class="fw-semibold fs-6 text-gray-500"><?= trans("occupancy_rate"); ?></span>
                                    <span class="fw-bold fs-6 text-gray-900"><?= $occupancyRate; ?>%</span>
                                </div>
                                <div class="h-8px w-100 bg-light rounded mb-3">
                                    <div class="<?= $occupancyColor; ?> rounded h-8px" role="progressbar" style="width: <?= $occupancyRate; ?>%;" aria-valuenow="<?= $occupancyRate; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="separator separator-dashed my-5"></div>
                    <div class="row g-5 mb-10">
                        <div class="col-sm-6 col-lg-3">
                            <div class="text-muted fw-semibold fs-7 mb-1"><?= trans("organizer"); ?></div>
                            <div class="text-gray-900 fw-bold fs-6 text-truncate" title="<?= esc($organizer); ?>"><?= esc($organizer); ?></div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="text-muted fw-semibold fs-7 mb-1"><?= trans("registration_deadline"); ?></div>
                            <div class="text-gray-900 fw-bold fs-6"><?= $regDeadline; ?></div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="text-muted fw-semibold fs-7 mb-1"><?= trans("access_level"); ?></div>
                            <div class="text-gray-900 fw-bold fs-6"><?= esc($regAccess); ?></div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="text-muted fw-semibold fs-7 mb-1"><?= trans("address"); ?></div>
                            <div class="text-gray-900 fw-semibold fs-7 d-block text-truncate mb-1" title="<?= esc($eventAddress); ?>">
                                <?= esc($eventAddress); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-6 mb-6">

        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header pt-5 border-0">
                    <h3 class="card-title fw-bold text-gray-900 fs-4"><?= esc($eventDetails->schedule_title ?? trans('event_schedule', 'raw')); ?></h3>
                </div>
                <div class="card-body pt-4 scroll-y mh-300px">
                    <?php if (!empty($scheduleList)): ?>
                        <div class="timeline-label">
                            <?php foreach ($scheduleList as $item): ?>
                                <div class="timeline-item">
                                    <div class="timeline-label fw-bold text-gray-800 fs-6"><?= esc($item->time ?? ''); ?></div>
                                    <div class="timeline-badge">
                                        <i class="fa fa-genderless text-success fs-1"></i>
                                    </div>
                                    <div class="timeline-content d-flex flex-column">
                                        <span class="fw-bold text-gray-800 fs-6"><?= esc($item->title ?? ''); ?></span>
                                        <?php if (!empty($item->description)): ?>
                                            <span class="text-muted fw-semibold fs-7 mt-1"><?= esc($item->description); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-muted text-center pt-10"><?= trans("no_records_found"); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header pt-5 border-0">
                    <h3 class="card-title fw-bold text-gray-900 fs-4"><?= esc($eventDetails->speakers_title ?? trans('speakers_guests', 'raw')); ?></h3>
                </div>
                <div class="card-body pt-4 scroll-y mh-300px">
                    <?php if (!empty($speakersList)): ?>
                        <div class="d-flex flex-column gap-5">
                            <?php foreach ($speakersList as $item):
                                $avatarUrl = getUserAvatar($item->avatarPath ?? '', $item->storage ?? 'local'); ?>
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-40px symbol-circle me-4">
                                        <img src="<?= esc($avatarUrl); ?>" class="object-fit-cover" alt=""/>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <span class="text-gray-900 fw-bold fs-6"><?= esc($item->name ?? ''); ?></span>
                                        <?php if (!empty($item->description)): ?>
                                            <span class="text-muted fw-semibold fs-7"><?= esc($item->description); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-muted text-center pt-10"><?= trans("no_records_found"); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h2><?= trans("participants"); ?>&nbsp;(<?= $totalParticipants; ?>)</h2>
            </div>

            <div class="card-toolbar">
                <div class="d-flex justify-content-end gap-3" data-kt-subscription-table-toolbar="base">
                    <form action="<?= $action ?? ''; ?>" method="post" class="kt-form">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="submit" value="export">
                        <button type="submit" class="btn btn-success" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            <i class="ki-duotone ki-file-down fs-2"><span class="path1"></span><span class="path2"></span></i>
                            <?= trans("export"); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body pt-5">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-20px">#</th>
                        <th class="min-w-150px"><?= trans('full_name'); ?></th>
                        <th class="min-w-150px"><?= trans('email'); ?></th>
                        <th class="min-w-200px"><?= trans("additional_info"); ?></th>
                        <th class="min-w-125px"><?= trans('date'); ?></th>
                        <th class="text-end min-w-70px"><?= trans('options'); ?></th>
                    </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">

                    <?php if (!empty($participants)):
                        $count = isset($pager) ? ($pager->getCurrentPage() - 1) * $pager->getPerPage() + 1 : 1;

                        foreach ($participants as $item):
                            $customData = [];
                            if (!empty($item->custom_data)) {
                                $customData = is_string($item->custom_data) ? json_decode(stripslashes($item->custom_data), true) : (array)$item->custom_data;
                            }
                            ?>
                            <tr>
                                <td><?= $count++; ?></td>
                                <td>
                                    <div class="text-gray-900 mb-1 fw-bold"><?= esc($item->full_name); ?></div>
                                    <div class="text-muted fs-7">
                                        <?php if (!empty($item->user_id)): ?>
                                            <span class="badge badge-light-primary px-3 py-1"><?= trans('member'); ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-light-secondary px-3 py-1"><?= trans('guest'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:<?= esc($item->email); ?>" class="text-gray-600 text-hover-primary">
                                        <?= esc($item->email); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (!empty($customData) && is_array($customData)): ?>
                                        <div class="d-flex flex-column gap-1">
                                            <?php foreach ($customData as $key => $val): ?>
                                                <div class="fs-8 text-gray-600">
                                                    <span class="fw-bold text-gray-800"><?= esc($key); ?>:</span>
                                                    <?= esc($val); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= formatDate($item->created_at); ?></td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        <?= trans('select'); ?><i class="ki-duotone ki-down fs-5 ms-2"></i>
                                    </a>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 menu-table-options py-4" data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <a href="javascript:void(0)" class="menu-link px-3 js-action-trigger"
                                               data-url="<?= esc($action); ?>"
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

            <?php if (empty($participants)): ?>
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