<?php
$eventDetails = $post->post_data?->details ?? null;
$eventDay = $eventDetails?->day ?? '';
$startTime = $eventDetails?->start_time ?? '09:00';
$endTime = $eventDetails?->end_time ?? '17:00';
$venueName = $eventDetails?->venue_name ?? '';
$locationAddress = $eventDetails?->location ?? '';
$organizer = $eventDetails?->organizer ?? '';
?>

    <div class="card card-flush w-100">
        <div class="card-header pt-5">
            <h3 class="card-title fw-bold text-dark fs-3"><?= trans("event_details") ?></h3>
        </div>

        <div class="card-body pt-0">

            <div class="fv-row mb-8">
                <label class="form-label fw-bold"><?= trans("start_date"); ?> & <?= trans("time"); ?></label>
                <div class="input-group">
                    <span class="input-group-text bg-light-primary border-0">
                        <i class="ki-duotone ki-calendar text-primary fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                    <input type="text" class="form-control form-control-solid ps-3" id="inputEventStartDate" name="event_start_date" placeholder="<?= trans("select_date", 'attr'); ?>"
                           value="<?= esc($post->event_start_date ?? ''); ?>"/>
                </div>
            </div>

            <div class="fv-row mb-8">
                <label class="form-label fw-bold"><?= trans("end_date"); ?> & <?= trans("time"); ?></label>
                <div class="input-group">
                    <span class="input-group-text bg-light-warning border-0">
                        <i class="ki-duotone ki-calendar text-warning fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                    <input type="text" class="form-control form-control-solid ps-3" id="inputEventEndDate" name="event_end_date" placeholder="<?= trans("select_date", 'attr'); ?>"
                           value="<?= esc($post->event_end_date ?? ''); ?>"/>
                </div>
            </div>

            <div class="fv-row mb-8">
                <label class="form-label"><?= trans("organizer"); ?></label>

                <div class="d-flex flex-column gap-2">
                    <div class="input-group">
                    <span class="input-group-text bg-light-emerald border-0">
                        <i class="ki-duotone ki-user fs-2 text-emerald">
                             <span class="path1"></span>
                             <span class="path2"></span>
                        </i>
                    </span>
                        <input type="text" class="form-control ps-3" name="event_organizer" placeholder="<?= trans("organizer", "attr"); ?>" value="<?= esc($organizer); ?>" max="255">
                    </div>
                </div>
            </div>

            <div class="fv-row mb-8">
                <label class="form-label"><?= trans("location"); ?></label>

                <div class="d-flex flex-column gap-2">
                    <div class="input-group">
                    <span class="input-group-text bg-light-danger border-0">
                        <i class="ki-duotone ki-geolocation text-danger fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                        <input type="text" class="form-control ps-3" name="event_venue_name" placeholder="<?= trans("venue_name", "attr"); ?>" value="<?= esc($venueName); ?>" max="255">
                    </div>
                    <textarea class="form-control" name="event_address" rows="4" placeholder="<?= trans("address", "attr"); ?>" maxlength="500"><?= esc($locationAddress); ?></textarea>
                </div>
            </div>

        </div>
    </div>

<?= $this->section('scripts'); ?>
    <script>
        $(document).ready(function () {
            if (typeof flatpickr === 'undefined') {
                console.error("Flatpickr library not found. Please include it.");
                return;
            }

            // Helper function to update day name
            function updateDayName(selectedDates) {
                if (selectedDates.length > 0) {
                    const dayName = selectedDates[0].toLocaleDateString('en-US', {weekday: 'long'});
                    $("#inputEventDay").val(dayName);
                } else {
                    $("#inputEventDay").val("");
                }
            }

            // Initialize Datepicker
            $("#inputEventStartDate, #inputEventEndDate").flatpickr({
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                time_24hr: true,
                minuteIncrement: 15
            });
        });
    </script>
<?= $this->endSection(); ?>