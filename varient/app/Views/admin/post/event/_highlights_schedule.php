<?php
$scheduleList = $post->post_data?->schedule ?? [];
$highlightList = $post->post_data?->highlights ?? [];
$eventDetails = $post->post_data?->details ?? null;
$eventScheduleTitle = $eventDetails?->schedule_title ?? trans('event_schedule', 'raw');
$eventHighlightsTitle = $eventDetails?->highlights_title ?? trans('event_highlights', 'raw');
?>

    <div class="row g-7 g-xl-10">
        <div class="col-12">
            <div class="card card-flush">
                <div class="card-header collapsible cursor-pointer rotate <?= !empty($post) ? 'collapsed' : 'active'; ?>" data-bs-toggle="collapse" data-bs-target="#schedule_card_collapsible">
                    <h3 class="card-title fw-bold fs-3 gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#78829D" class="bi bi-stopwatch-fill" viewBox="0 0 16 16">
                            <path d="M6.5 0a.5.5 0 0 0 0 1H7v1.07A7.001 7.001 0 0 0 8 16a7 7 0 0 0 5.29-11.584l.013-.012.354-.354.353.354a.5.5 0 1 0 .707-.707l-1.414-1.415a.5.5 0 1 0-.707.707l.354.354-.354.354-.012.012A6.97 6.97 0 0 0 9 2.071V1h.5a.5.5 0 0 0 0-1zm2 5.6V9a.5.5 0 0 1-.5.5H4.5a.5.5 0 0 1 0-1h3V5.6a.5.5 0 1 1 1 0"/>
                        </svg>
                        <?= trans("event_schedule"); ?>
                    </h3>
                    <div class="card-toolbar rotate-180">
                        <i class="ki-duotone ki-down fs-1"></i>
                    </div>
                </div>
                <div id="schedule_card_collapsible" class="collapse <?= empty($post) ? 'show' : ''; ?>">
                    <div class="card-body pt-5">

                        <div class="fw-row mb-6">
                            <label class="form-label"><?= trans("section_title"); ?></label>
                            <input type="text" class="form-control" name="event_schedule_title" placeholder="<?= trans("title"); ?>" maxlength="255" value="<?= esc($eventScheduleTitle); ?>">
                        </div>

                        <div id="event-schedule-container" class="d-flex flex-column gap-3 mb-3">
                            <?php if (!empty($scheduleList)):
                                foreach ($scheduleList as $item):
                                    $scheduleId = uniqid();
                                    $scheduleTime = $item->time ?? '';
                                    $scheduleTitle = $item->title ?? '';
                                    $scheduleDesc = $item->description ?? ''; ?>
                                    <div class="fv-row d-flex gap-3 align-items-center event-schedule">
                                        <input type="text" name="event_schedule[<?= $scheduleId; ?>][time]" value="<?= esc($scheduleTime); ?>" class="form-control" placeholder="E.g. 09:00 - 09:30">
                                        <input type="text" name="event_schedule[<?= $scheduleId; ?>][title]" value="<?= esc($scheduleTitle); ?>" class="form-control" placeholder="<?= trans("title"); ?>">
                                        <input type="text" name="event_schedule[<?= $scheduleId; ?>][description]" value="<?= esc($scheduleDesc); ?>" class="form-control" placeholder="<?= trans("description"); ?>">
                                        <button type="button" class="btn btn-sm btn-icon flex-shrink-0 btn-light-danger btn-remove-schedule">
                                            <i class="ki-duotone ki-trash fs-5">
                                                <span class="path1"></span><span class="path2"></span>
                                                <span class="path3"></span><span class="path4"></span><span class="path5"></span>
                                            </i>
                                        </button>
                                    </div>
                                <?php endforeach;
                            endif; ?>
                        </div>

                        <div class="mb-6 fv-row">
                            <button type="button" id="btn-add-schedule" class="btn btn-sm btn-light-success">
                                <i class="ki-duotone ki-plus fs-4"></i>
                                <?= trans("add_new"); ?>
                            </button>
                        </div>

                        <div class="text-gray-500 mt-1 fw-semibold fs-7"><?= trans("event_schedule_ex"); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card card-flush">
                <div class="card-header collapsible cursor-pointer rotate <?= !empty($post) ? 'collapsed' : 'active'; ?>" data-bs-toggle="collapse" data-bs-target="#highlights_card_collapsible">
                    <h3 class="card-title fw-bold fs-3 gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#78829D" viewBox="0 0 16 16">
                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/>
                        </svg>
                        <?= trans("event_highlights"); ?>
                    </h3>
                    <div class="card-toolbar rotate-180">
                        <i class="ki-duotone ki-down fs-1"></i>
                    </div>
                </div>
                <div id="highlights_card_collapsible" class="collapse <?= empty($post) ? 'show' : ''; ?>">
                    <div class="card-body pt-5">

                        <div class="fw-row mb-6">
                            <label class="form-label"><?= trans("section_title"); ?></label>
                            <input type="text" class="form-control" name="event_highlights_title" placeholder="<?= trans("title"); ?>" maxlength="255" value="<?= esc($eventHighlightsTitle); ?>">
                        </div>

                        <div id="highlights-container" class="d-flex flex-column gap-3 mb-3">
                            <?php if (!empty($highlightList)):
                                foreach ($highlightList as $item):
                                    $highlightId = uniqid();
                                    $highlightTitle = $item->t ?? '';
                                    $highlightVal = $item->v ?? ''; ?>
                                    <div class="fv-row d-flex gap-3 align-items-center event-highlights">
                                        <input type="text" name="event_highlight[<?= $highlightId; ?>][title]" value="<?= esc($highlightTitle); ?>" class="form-control" placeholder="<?= trans("title"); ?>">
                                        <input type="text" name="event_highlight[<?= $highlightId; ?>][value]" value="<?= esc($highlightVal); ?>" class="form-control" placeholder="<?= trans("value"); ?>">
                                        <button type="button" class="btn btn-sm btn-icon flex-shrink-0 btn-light-danger btn-remove-highlight">
                                            <i class="ki-duotone ki-trash fs-5">
                                                <span class="path1"></span><span class="path2"></span>
                                                <span class="path3"></span><span class="path4"></span><span class="path5"></span>
                                            </i>
                                        </button>
                                    </div>
                                <?php endforeach;
                            endif; ?>
                        </div>

                        <div class="mb-6 fv-row">
                            <button type="button" id="btn-add-highlight" class="btn btn-sm btn-light-success">
                                <i class="ki-duotone ki-plus fs-4"></i>
                                <?= trans("add_new"); ?>
                            </button>
                        </div>

                        <div class="text-gray-500 mt-1 fw-semibold fs-7"><?= trans("event_highlight_ex"); ?></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

<?= $this->section('scripts'); ?>
    <script>
        $(document).ready(function () {
            var $scheduleContainer = $('#event-schedule-container');
            $('#btn-add-schedule').on('click', function () {
                const scheduleId = Date.now() + '-' + Math.floor(Math.random() * 1000);
                var newOptionHTML = `
            <div class="fv-row d-flex gap-3 align-items-center event-schedule">
                <input type="text" name="event_schedule[${scheduleId}][time]" class="form-control" placeholder="E.g. 09:00 - 09:30">
                <input type="text" name="event_schedule[${scheduleId}][title]" class="form-control" placeholder="<?= trans("title", "js"); ?>">
                <input type="text" name="event_schedule[${scheduleId}][description]" class="form-control" placeholder="<?= trans("description", "js"); ?>">
                <button type="button" class="btn btn-sm btn-icon flex-shrink-0 btn-light-danger btn-remove-schedule">
                    <i class="ki-duotone ki-trash fs-5">
                        <span class="path1"></span><span class="path2"></span>
                        <span class="path3"></span><span class="path4"></span><span class="path5"></span>
                    </i>
                </button>
            </div>`;
                $scheduleContainer.append(newOptionHTML);
            });

            $(document).on('click', '.btn-remove-schedule', function () {
                Swal.fire(swalOptions(VrConfig.text.confirmDelete)).then((result) => {
                    if (result.isConfirmed) {
                        $(this).closest('.event-schedule').remove();
                    }
                });
            });
        });

        $(document).ready(function () {
            var $highlightsContainer = $('#highlights-container');
            $('#btn-add-highlight').on('click', function () {
                const highlightId = Date.now() + '-' + Math.floor(Math.random() * 1000);
                var newOptionHTML = `
            <div class="fv-row d-flex gap-3 align-items-center event-highlight">
                <input type="text" name="event_highlight[${highlightId}][title]" class="form-control" placeholder="<?= trans("title", "js"); ?>">
                <input type="text" name="event_highlight[${highlightId}][value]" class="form-control" placeholder="<?= trans("value", "js"); ?>">
                <button type="button" class="btn btn-sm btn-icon flex-shrink-0 btn-light-danger btn-remove-highlight">
                    <i class="ki-duotone ki-trash fs-5">
                        <span class="path1"></span><span class="path2"></span>
                        <span class="path3"></span><span class="path4"></span><span class="path5"></span>
                    </i>
                </button>
            </div>`;
                $highlightsContainer.append(newOptionHTML);
            });

            $(document).on('click', '.btn-remove-highlight', function () {
                Swal.fire(swalOptions(VrConfig.text.confirmDelete)).then((result) => {
                    if (result.isConfirmed) {
                        $(this).closest('.event-highlight').remove();
                    }
                });
            });
        });
    </script>
<?= $this->endSection(); ?>