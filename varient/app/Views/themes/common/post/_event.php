<?php
$eventDetails = $post->post_data?->details ?? null;
$eventHighlightsTitle = $eventDetails?->highlights_title ?? trans('event_highlights', 'raw');
$eventScheduleTitle = $eventDetails?->schedule_title ?? trans('event_schedule', 'raw');
$speakersTitle = $eventDetails?->speakers_title ?? trans('speakers_guests', 'raw');
$eventStartDateRaw = $post->event_start_date ?? '';
$eventEndDateRaw = $post->event_end_date ?? '';

$displayDate = '--';
$displayDay = '--';
$displayTime = '--';

if (!empty($eventStartDateRaw)) {
    $st = strtotime($eventStartDateRaw);
    $et = !empty($eventEndDateRaw) ? strtotime($eventEndDateRaw) : $st;

    $isSameDay = date('Y-m-d', $st) === date('Y-m-d', $et);

    if ($isSameDay) {
        $displayDate = formatDateClient(date('Y-m-d', $st));
        $displayDay = trans(strtolower(date('l', $st)));
        $displayTime = date('H:i', $st) . ' - ' . date('H:i', $et);
    } else {
        $displayDate = formatDateClient(date('Y-m-d', $st)) . ' — ' . formatDateClient(date('Y-m-d', $et));
        $displayDay = trans("multiple_days");
        if ($displayDay === 'multiple_days') {
            $displayDay = 'Multiple Days';
        }
        $displayTime = date('H:i', $st) . ' - ' . date('H:i', $et);
    }
}

$eventVenueName = $eventDetails?->venue_name ?? '';
$eventLocation = $eventDetails?->location ?? '';
$organizer = $eventDetails?->organizer ?? '';
$address = $eventDetails?->map_address ?? '';
$latitude = $eventDetails?->map_latitude ?? '';
$longitude = $eventDetails?->map_longitude ?? '';

$scheduleList = $post->post_data->schedule ?? [];
$highlightList = $post->post_data->highlights ?? [];
$speakerList = $post->post_data->speakers ?? [];
$customFields = $post->post_data->registration_fields ?? [];

$paywallType = !empty($premiumMembership->paywallAppearance) ? $premiumMembership->paywallAppearance : 'fade';
$isLoggedIn = authCheck();
$user = $isLoggedIn ? user() : null;

// Registration Variables
$regType = $eventDetails?->registration_type ?? 'none';
$regUrl = $eventDetails?->registration_button_url ?? '';
$regBtnText = !empty($eventDetails?->registration_button_text) ? $eventDetails->registration_button_text : trans("register_now");
$regAccess = $eventDetails?->registration_access ?? 'public';
$regDeadline = $eventDetails?->registration_deadline ?? '';

// User Registration Status
$isUserRegistered = isset($isUserRegistered) ? $isUserRegistered : false;

// Time and Date Computations
$nowObj = timeNowObject();
$nowTimestamp = $nowObj->getTimestamp();
$timezone = getContextValue('config')?->timezone ?? app_timezone();

// Determine Registration Capacity and Current Count
$eventCapacity = !empty($eventDetails->registration_capacity) ? (int)$eventDetails->registration_capacity : 0;
$registrationCount = $registrationCount ?? 0;

$isRegistrationClosed = false;

// Check Capacity Limits
if ($eventCapacity > 0 && $registrationCount >= $eventCapacity) {
    $isRegistrationClosed = true;
}

// Check Deadline Limits (if capacity isn't already full)
if (!$isRegistrationClosed && !empty($regDeadline)) {
    try {
        $deadlineTimestamp = \CodeIgniter\I18n\Time::parse($regDeadline, $timezone)->getTimestamp();

        if ($deadlineTimestamp < $nowTimestamp) {
            $isRegistrationClosed = true;
        }
    } catch (\Exception $e) {
        $isRegistrationClosed = false;
    }
}

?>

<?= loadCommonView('post/_post_image_preview', ['post' => $post, 'postImages' => $postImages]); ?>

<?php if (!$hasAccess && $paywallType === 'hard'): ?>
    <div class="mt-5 mb-5">
        <?= loadCommonView('premium/_paywall', [
                'restrictionType' => $restrictionType,
                'paywallType'     => 'hard'
        ]); ?>
    </div>
<?php else: ?>
    <div class="event-wrapper">
        <div class="event-info-bar">

            <div class="event-meta-group">
                <div class="event-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 2v4"/>
                        <path d="M16 2v4"/>
                        <rect width="18" height="18" x="3" y="4" rx="2"/>
                        <path d="M3 10h18"/>
                    </svg>
                </div>
                <div class="meta-content">
                    <span class="meta-label"><?= trans("date"); ?></span>
                    <div class="meta-value" style="word-break: break-word;"><?= esc($displayDate); ?></div>
                    <span class="meta-sub"><?= esc($displayDay); ?></span>
                </div>
            </div>

            <div class="event-meta-group">
                <div class="event-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 6v6l4 2"/>
                        <circle cx="12" cy="12" r="10"/>
                    </svg>
                </div>
                <div class="meta-content">
                    <span class="meta-label"><?= trans("time"); ?></span>
                    <div class="meta-value"><?= esc($displayTime); ?></div>
                    <span class="meta-sub"><?= trans("start"); ?> / <?= trans("end"); ?></span>
                </div>
            </div>

            <div class="event-meta-group">
                <div class="event-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                </div>
                <div class="meta-content">
                    <span class="meta-label"><?= trans("location"); ?></span>
                    <div class="meta-value"><?= esc($eventVenueName); ?></div>
                </div>
            </div>
        </div>

        <?php if (!$hasAccess && $paywallType === 'fade'): ?>
        <div style="position: relative; overflow: hidden; min-height: 520px;">
            <?php endif; ?>

            <?php if (!empty($post->content)): ?>
                <div class="event-text">
                    <h3 class="event-section-title"><?= trans("about_event"); ?></h3>
                    <div class="entry-content">
                        <?= $post->content; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!$hasAccess && $paywallType === 'fade'): ?>
            <?= loadCommonView('premium/_paywall', [
                    'restrictionType' => $restrictionType,
                    'paywallType'     => 'fade'
            ]); ?>
        </div>
    <?php else: ?>

        <?php if (!empty($highlightList) && countItems($highlightList) > 0): ?>
            <h3 class="event-section-title"><?= esc($eventHighlightsTitle); ?></h3>
            <div class="highlights-grid">
                <?php foreach ($highlightList as $item): ?>
                    <div class="highlight-item">
                        <div class="highlight-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="m9 12 2 2 4-4"/>
                            </svg>
                        </div>
                        <div>
                            <span class="highlight-title"><?= esc($item->t); ?></span>
                            <?= esc($item->v); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($speakerList) && countItems($speakerList) > 0): ?>
            <h3 class="event-section-title"><?= esc($speakersTitle); ?></h3>
            <div class="speaker-list-grid">
                <?php foreach ($speakerList as $item):
                    $avatarUrl = getStorageFileUrl($item->avatarPath ?? '', $item->storage ?? '', 'user.png'); ?>
                    <div class="speaker-list-item">
                        <div class="speaker-avatar-container">
                            <img src="<?= esc($avatarUrl); ?>" class="speaker-avatar-img" alt="<?= esc($item->name ?? '', 'attr'); ?>" loading="lazy">
                        </div>
                        <div>
                            <div class="fw-bold"><?= esc($item->name ?? ''); ?></div>
                            <div class="small"><?= esc($item->description ?? ''); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($scheduleList) && countItems($scheduleList) > 0): ?>
            <h3 class="event-section-title"><?= esc($eventScheduleTitle); ?></h3>
            <section class="schedule-section">
                <div class="schedule-timeline">
                    <?php foreach ($scheduleList as $item): ?>
                        <div class="schedule-item">
                            <div class="schedule-time-badge"><?= esc($item->time); ?></div>
                            <h3 class="schedule-item-title"><?= esc($item->title); ?></h3>
                            <p class="schedule-item-desc"><?= esc($item->description); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($regType !== 'none'): ?>
            <div class="event-registration-cta mt-5 mb-5">
                <div class="cta-content">
                    <h3 class="cta-title"><?= trans("join_this_event"); ?></h3>
                    <p class="cta-desc"><?= trans("join_this_event_exp"); ?></p>

                    <?php if (!empty($regDeadline) && !$isRegistrationClosed && !$isUserRegistered): ?>
                        <div class="cta-deadline">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="deadline-icon">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            <div class="deadline-text">
                                <span class="deadline-label"><?= trans("registration_deadline"); ?>:</span>
                                <span class="deadline-value"><?= esc(formatDateClient($regDeadline)); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="cta-action">
                    <?php if ($regType === 'external' && !empty($regUrl)): ?>
                        <a href="<?= esc($regUrl); ?>" class="btn-register-event" target="_blank" rel="noopener noreferrer">
                            <span class="btn-text"><?= esc($regBtnText); ?></span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14"/>
                                <path d="m12 5 7 7-7 7"/>
                            </svg>
                        </a>
                    <?php elseif ($isLoggedIn && $isUserRegistered): ?>
                        <div class="registration-confirmed d-flex align-items-center gap-2 py-2 px-4 rounded-pill">
                            <div class="icon-box text-success">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 6 9 17l-5-5"/>
                                </svg>
                            </div>
                            <span class="fw-bold"><?= trans("event_you_are_registered"); ?></span>
                        </div>
                    <?php elseif ($isRegistrationClosed): ?>
                        <div class="registration-status-badge">
                            <div class="icon-box text-danger">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="15" y1="9" x2="9" y2="15"/>
                                    <line x1="9" y1="9" x2="15" y2="15"/>
                                </svg>
                            </div>
                            <span class="fw-bold"><?= trans("registration_closed"); ?></span>
                        </div>
                    <?php elseif ($regType === 'internal'): ?>
                        <?php if ($regAccess === 'registered' && !$isLoggedIn): ?>
                            <button type="button" class="btn-register-event" data-bs-toggle="modal" data-bs-target="#loginModal">
                                <span class="btn-text"><?= esc($regBtnText); ?></span>
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn-register-event" data-bs-toggle="modal" data-bs-target="#eventRegistrationModal">
                                <span class="btn-text"><?= esc($regBtnText); ?></span>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?= loadCommonView('post/_faq', ['post' => $post]); ?>

        <div class="bottom-info-stack">
            <?php if (!empty($organizer)): ?>
                <div class="card card-organizer border-0 shadow-sm p-4 rounded-4">
                    <div class="row align-items-center">
                        <div class="col-12">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-organizer">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M10 12h4"/>
                                        <path d="M10 8h4"/>
                                        <path d="M14 21v-3a2 2 0 0 0-4 0v3"/>
                                        <path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"/>
                                        <path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"/>
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="title"><?= trans("organizer"); ?></h5>
                                    <div class="sub-title"><?= esc($organizer); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?= loadCommonView('partials/_map_event', [
                    'latitude'       => $latitude,
                    'longitude'      => $longitude,
                    'address'        => $address,
                    'eventVenueName' => $eventVenueName,
            ]); ?>
        </div>

    <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($regType === 'internal'): ?>
    <div class="custom-modal-wrapper">
        <div class="modal fade post-format-modal modal-registration" id="eventRegistrationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?= trans("event_registration"); ?></h5>
                        <p class="modal-subtitle mb-0"><?= trans("event_registration_exp"); ?></p>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form action="<?= base_url('register-event-post'); ?>" method="post" id="formEventRegistration" class="event-registration-form needs-validation" novalidate>
                        <?= csrf_field(); ?>
                        <input type="hidden" name="post_id" value="<?= $post->id; ?>">

                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-12 col-lg-6">
                                    <label class="form-label"><?= trans("full_name"); ?></label>
                                    <input type="text" name="full_name" class="form-control form-input" placeholder="<?= trans("full_name", "attr"); ?>" required>
                                </div>
                                <div class="col-md-12 col-lg-6">
                                    <label class="form-label"><?= trans("email"); ?></label>
                                    <input type="text" name="email" class="form-control form-input" value="<?= $isLoggedIn ? esc($user->email ?? '') : ''; ?>" placeholder="<?= trans("email", "attr"); ?>" required>
                                </div>

                                <?php if (!empty($customFields)):
                                    foreach ($customFields as $index => $field):
                                        $inputName = 'custom_field_' . $index;
                                        $fieldLabel = $field->label ?? '';
                                        $fieldType = $field->type ?? 'text';
                                        $options = isset($field->options) && !empty($field->options) ? array_map('trim', explode(',', $field->options)) : [];
                                        ?>
                                        <div class="col-md-12 col-lg-6">
                                            <label class="form-label"><?= esc($fieldLabel); ?></label>
                                            <input type="hidden" name="custom_labels[<?= $inputName; ?>]" value="<?= esc($fieldLabel); ?>">

                                            <?php if ($fieldType === 'dropdown' && !empty($options)): ?>
                                                <select name="custom_data[<?= $inputName; ?>]" class="form-select" required>
                                                    <option value=""><?= trans("select_an_option"); ?></option>
                                                    <?php foreach ($options as $opt): ?>
                                                        <option value="<?= esc($opt); ?>"><?= esc($opt); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            <?php else: ?>
                                                <input type="text" name="custom_data[<?= $inputName; ?>]" class="form-control form-input" placeholder="<?= esc($fieldLabel, "attr"); ?>" required>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach;
                                endif; ?>
                            </div>

                            <div class="mt-5 mb-2 text-end">
                                <button type="submit" class="btn btn-custom px-5 py-3 rounded-pill fw-semibold w-100 shadow-sm" id="btnEventRegistrationSubmit">
                                    <?= trans("complete_registration"); ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?= $this->section('scripts'); ?>
    <script>
        const EventRegLangs = {
            processing: <?= json_encode(trans('processing')); ?>,
            defaultSuccess: <?= json_encode(trans('registration_successful')); ?>,
            defaultError: <?= json_encode(trans('msg_error')); ?>,
            networkError: "Network or server error occurred.",
            btnOk: <?= json_encode(trans('ok')); ?>
        };

        $(document).ready(function () {
            $('#formEventRegistration').on('submit', function (event) {
                event.preventDefault();

                // Form Validation Check
                if (!this.checkValidity()) {
                    event.stopPropagation();
                    $(this).addClass('was-validated');
                    return;
                }
                $(this).addClass('was-validated');

                var $form = $(this);
                var $submitBtn = $('#btnEventRegistrationSubmit');
                var originalBtnText = $submitBtn.html();

                // Disable button and add loading spinner with translated text
                $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> ' + EventRegLangs.processing);

                // Send AJAX Request
                $.ajax({
                    url: $form.attr('action'),
                    type: $form.attr('method'),
                    data: $form.serialize(),
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 1) {
                            Swal.fire({
                                text: response.message || EventRegLangs.defaultSuccess,
                                icon: "success",
                                buttonsStyling: false,
                                confirmButtonText: EventRegLangs.btnOk,
                                customClass: {confirmButton: "btn btn-primary"}
                            }).then(function (result) {
                                if (result.isConfirmed) {
                                    $('#eventRegistrationModal').modal('hide');
                                    window.location.reload();
                                }
                            });
                        } else {
                            Swal.fire({
                                text: response.message || EventRegLangs.defaultError,
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: EventRegLangs.btnOk,
                                customClass: {confirmButton: "btn btn-danger"}
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            text: EventRegLangs.networkError,
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: EventRegLangs.btnOk,
                            customClass: {confirmButton: "btn btn-danger"}
                        });
                    },
                    complete: function () {
                        // Restore the original button state and text
                        $submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                });
            });
        });
    </script>
    <?= $this->endSection(); ?>
<?php endif; ?>