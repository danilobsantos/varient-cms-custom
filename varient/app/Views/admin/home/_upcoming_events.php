<div class="card card-flush h-100">
    <div class="card-header pt-7">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold text-dark"><?= trans("upcoming_events"); ?></span>
            <span class="text-gray-500 mt-1 fw-semibold fs-6"><?= trans("upcoming_events_exp"); ?></span>
        </h3>
        <div class="card-toolbar">
            <a href="<?= adminUrl('events'); ?>" class="btn btn-sm btn-light"><?= trans("view_all"); ?></a>
        </div>
    </div>
    <div class="card-body pt-5">
        <?php if (!empty($upcomingEvents)):
            $themeColors = ['primary', 'danger', 'success', 'warning', 'info'];
            $totalColors = count($themeColors);
            $totalEvents = count($upcomingEvents);

            foreach ($upcomingEvents as $index => $event):
                $badgeColor = $themeColors[$index % $totalColors];
                $startTimestamp = !empty($event->event_start_date) ? strtotime($event->event_start_date) : time();
                $month = date('M', $startTimestamp);
                $day = date('d', $startTimestamp);
                $startTime = date('H:i', $startTimestamp);
                $endTime = !empty($event->event_end_date) ? date('H:i', strtotime($event->event_end_date)) : '';
                $timeString = $endTime ? $startTime . ' - ' . $endTime : $startTime;
                $fullDate = date('d M Y', $startTimestamp);
                $dateTimeDisplay = $fullDate . ' • ' . $timeString;
                $marginClass = ($index === $totalEvents - 1) ? '' : 'mb-7';
                ?>

                <div class="d-flex flex-stack <?= $marginClass; ?>">
                    <div class="d-flex align-items-center">

                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-<?= $badgeColor; ?> text-<?= $badgeColor; ?> d-flex flex-column justify-content-center align-items-center">
                                <span class="fs-7 fw-semibold"><?= esc($month); ?></span>
                                <span class="fs-3 fw-bold"><?= esc($day); ?></span>
                            </span>
                        </div>

                        <div class="d-flex flex-column">
                            <a href="<?= generatePostUrl($event); ?>" class="text-dark text-hover-primary fw-bold fs-5">
                                <?= esc($event->title); ?>
                            </a>
                            <span class="text-muted fw-semibold fs-7">
                                <i class="ki-duotone ki-calendar fs-7 me-1"><span class="path1"></span><span class="path2"></span></i>
                                <?= esc($dateTimeDisplay); ?>
                            </span>
                        </div>

                    </div>

                    <div class="d-flex flex-column align-items-end">
                        <a href="<?= adminUrl('events/event/' . esc($event->id)); ?>" class="btn btn-sm btn-light">
                            <?= trans("details"); ?>
                        </a>
                    </div>
                </div>

            <?php endforeach;
        else: ?>
            <div class="d-flex flex-column align-items-center justify-content-center h-100 py-10">
                <span class="text-muted fw-semibold fs-6">
                    <?= trans("no_records_found"); ?>
                </span>
            </div>
        <?php endif; ?>
    </div>
</div>