<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <div class="row justify-content-center">
        <div class="col-12 col-xxl-10">
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h2 class="fw-bold"><?= trans("events_calendar"); ?></h2>
                    </div>
                    <div class="card-toolbar">
                        <a href="<?= adminUrl('add-post?format=event'); ?>" class="btn btn-primary">
                            <i class="ki-duotone ki-plus fs-2"></i> <?= trans("add_event"); ?>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div id="calendar_app"></div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .fc-event, .fc-event-main {
            cursor: pointer !important;
        }

        .fc-daygrid-dot-event {
            background-color: #dc3545 !important;
            color: #ffffff !important;
            padding: 2px 5px !important;
            border-radius: 4px;
            border: none !important;
        }

        .fc-direction-ltr .fc-daygrid-event .fc-daygrid-event-dot {
            display: none !important;
        }

        .fc-daygrid-dot-event .fc-event-title,
        .fc-daygrid-dot-event .fc-event-time {
            color: #ffffff !important;
            font-weight: 500;
        }

        .fc-daygrid-dot-event:hover {
            background-color: #c82333 !important;
        }
    </style>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>

    <script src="<?= base_url("assets/admin/plugins/fullcalendar-6.1.20/index.global.min.js") ?>"></script>

    <script>
        const CalendarConfig = {
            today: <?= json_encode(trans('today')); ?>,
            year: <?= json_encode(trans('year')); ?>,
            month: <?= json_encode(trans('month')); ?>,
            week: <?= json_encode(trans('week')); ?>,
            day: <?= json_encode(ucfirst(trans('day') ?? '')); ?>,
            list: <?= json_encode(trans('list')); ?>,
            noEvents: <?= json_encode(trans('no_records_found') ?? 'No events to display'); ?>,
            locale: <?= json_encode(str_replace('_', '-', $activeLang->short_form)); ?>,
        };

        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar_app');

            if (!calendarEl) {
                return;
            }

            // Initialize FullCalendar
            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: CalendarConfig.locale,
                noEventsText: CalendarConfig.noEvents,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'multiMonthYear,dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                },
                buttonText: {
                    today: CalendarConfig.today,
                    month: CalendarConfig.month,
                    week: CalendarConfig.week,
                    day: CalendarConfig.day,
                    list: CalendarConfig.list
                },
                initialDate: new Date(),
                navLinks: true,
                selectable: true,
                selectMirror: true,
                dayMaxEvents: true,
                themeSystem: 'bootstrap5',

                // Fetch dynamic JSON data from Controller
                events: VrConfig.adminUrl + '/events/feed',

                // Direct Navigation on Event Click (No Modal)
                eventClick: function (arg) {
                    // Prevent default anchor behavior
                    arg.jsEvent.preventDefault();

                    var props = arg.event.extendedProps;

                    // Open the details URL in a new tab
                    if (props.details_url) {
                        window.open(props.details_url, '_blank');
                    }
                }
            });

            // Render the calendar
            calendar.render();
        });
    </script>
<?= $this->endSection(); ?>