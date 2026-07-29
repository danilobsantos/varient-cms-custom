<?php
$eventDetails = $post->post_data?->details ?? null;

// Registration Settings
$regType = $eventDetails?->registration_type ?? 'none';
$regUrl = $eventDetails?->registration_button_url ?? '';
$regBtnText = $eventDetails?->registration_button_text ?? 'Register Now';

// Internal Registration Settings
$regCapacity = $eventDetails?->registration_capacity ?? '';
$regDeadline = $eventDetails?->registration_deadline ?? '';
$regStatus = $eventDetails?->registration_status ?? 'approved';
$regSuccessMsg = $eventDetails?->registration_success_message ?? 'Your registration is successful!';
$regAccess = $eventDetails?->registration_access ?? 'public';

// Custom Fields
$customFields = $post->post_data?->registration_fields ?? [];
?>

    <div class="card card-flush">
        <div class="card-header collapsible cursor-pointer rotate <?= !empty($post) ? 'collapsed' : 'active'; ?>" data-bs-toggle="collapse" data-bs-target="#registration_card_collapsible">
            <h3 class="card-title fw-bold fs-3 gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#78829D" viewBox="0 0 16 16">
                    <path d="M0 4.5A1.5 1.5 0 0 1 1.5 3h13A1.5 1.5 0 0 1 16 4.5V6a.5.5 0 0 1-.5.5 1.5 1.5 0 0 0 0 3 .5.5 0 0 1 .5.5v1.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 11.5V10a.5.5 0 0 1 .5-.5 1.5 1.5 0 1 0 0-3A.5.5 0 0 1 0 6zm4-1v1h1v-1zm1 3v-1H4v1zm7 0v-1h-1v1zm-1-2h1v-1h-1zm-6 3H4v1h1zm7 1v-1h-1v1zm-7 1H4v1h1zm7 1v-1h-1v1zm-8 1v1h1v-1zm7 1h1v-1h-1z"/>
                </svg>
                <?= trans("event_registration_and_tickets") ?>
            </h3>
            <div class="card-toolbar rotate-180">
                <i class="ki-duotone ki-down fs-1"></i>
            </div>
        </div>
        <div id="registration_card_collapsible" class="collapse <?= empty($post) ? 'show' : ''; ?>">
            <div class="card-body pt-5">

                <div class="fv-row mb-8">
                    <label class="form-label fw-bold"><?= trans("registration_type"); ?></label>
                    <select class="form-select form-select-solid" name="registration_type" id="registration_type_select">
                        <option value="none" <?= $regType === 'none' ? 'selected' : ''; ?>><?= trans("no_registration_required"); ?></option>
                        <option value="external" <?= $regType === 'external' ? 'selected' : ''; ?>><?= trans("event_external_link_label"); ?></option>
                        <option value="internal" <?= $regType === 'internal' ? 'selected' : ''; ?>><?= trans("internal_registration"); ?></option>
                    </select>
                </div>

                <div id="common_reg_container" <?= $regType !== 'none' ? '' : 'style="display:none;"'; ?>>
                    <div class="fv-row mb-6">
                        <label class="form-label"><?= trans("button_text"); ?></label>
                        <input type="text" class="form-control" name="registration_button_text" placeholder="e.g., <?= trans("register_now", "attr"); ?>" value="<?= esc($regBtnText); ?>">
                    </div>
                </div>

                <div id="external_reg_container" class="reg-container" <?= $regType === 'external' ? '' : 'style="display:none;"'; ?>>
                    <div class="fv-row mb-6">
                        <label class="form-label"><?= trans("redirect_url"); ?></label>
                        <input type="url" class="form-control" name="registration_button_url" placeholder="https://" value="<?= esc($regUrl); ?>">
                    </div>
                </div>

                <div id="internal_reg_container" class="reg-container border border-dashed border-gray-300 rounded p-6" <?= $regType === 'internal' ? '' : 'style="display:none;"'; ?>>
                    <h4 class="mb-5 text-dark"><?= trans("registration_rules"); ?></h4>

                    <div class="row g-5 mb-6">
                        <div class="col-xl-12 col-xxl-6">
                            <label class="form-label"><?= trans("capacity_limit"); ?></label>
                            <input type="number" class="form-control" name="registration_capacity" placeholder="<?= trans("capacity_limit", "attr") ?>" value="<?= esc($regCapacity); ?>" min="1">
                        </div>
                        <div class="col-xl-12 col-xxl-6">
                            <label class="form-label"><?= trans("registration_deadline"); ?></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light-primary border-0">
                                    <i class="ki-duotone ki-calendar text-primary fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </span>
                                <input type="text" class="form-control form-control-solid ps-3" id="inputRegDeadlinePicker" name="registration_deadline" placeholder="<?= trans("select_date", 'attr'); ?>" value="<?= esc($regDeadline); ?>"/>
                            </div>
                        </div>
                    </div>

                    <div class="row g-5 mb-6">
                        <div class="col-xl-12 col-xxl-6">
                            <label class="form-label"><?= trans("access_level"); ?></label>
                            <select class="form-select" name="registration_access">
                                <option value="public" <?= $regAccess === 'public' ? 'selected' : ''; ?>><?= trans("public_open_to_everyone"); ?></option>
                                <option value="registered" <?= $regAccess === 'registered' ? 'selected' : ''; ?>><?= trans("registered_users_only"); ?></option>
                            </select>
                        </div>
                        <div class="col-xl-12 col-xxl-6">
                            <label class="form-label"><?= trans("success_message"); ?></label>
                            <input type="text" class="form-control" name="registration_success_message" placeholder="<?= trans("success_message_exp", "attr"); ?>" value="<?= esc($regSuccessMsg); ?>">
                        </div>
                    </div>

                    <div class="separator separator-dashed border-gray-300 my-8"></div>

                    <h4 class="mb-2 text-dark"><?= trans("custom_form_fields"); ?></h4>
                    <div class="text-gray-600 mb-5 fs-7"><?= trans("custom_form_fields_exp"); ?></div>

                    <div id="custom-fields-container" class="d-flex flex-column gap-3 mb-4">
                        <?php if (!empty($customFields)):
                            foreach ($customFields as $item):
                                $fieldId = uniqid();
                                $fieldLabel = $item->label ?? '';
                                $fieldType = $item->type ?? 'text';
                                $fieldOptions = $item->options ?? ''; ?>
                                <div class="fv-row d-flex gap-3 align-items-start custom-field-row bg-light p-3 rounded border border-gray-200">
                                    <div class="flex-grow-1">
                                        <input type="text" name="reg_fields[<?= $fieldId; ?>][label]" value="<?= esc($fieldLabel); ?>" class="form-control mb-2" placeholder="<?= trans("field_label", "attr"); ?> (e.g., <?= trans("company_name", "attr"); ?>)">
                                        <input type="text" name="reg_fields[<?= $fieldId; ?>][options]" value="<?= esc($fieldOptions); ?>" class="form-control field-options-input" placeholder="<?= trans("comma_separated_options", "attr"); ?>" <?= $fieldType === 'dropdown' ? '' : 'style="display:none;"'; ?>>
                                    </div>
                                    <div class="w-200px">
                                        <select class="form-select custom-field-type-select" name="reg_fields[<?= $fieldId; ?>][type]">
                                            <option value="text" <?= $fieldType === 'text' ? 'selected' : ''; ?>><?= trans("text_input"); ?></option>
                                            <option value="dropdown" <?= $fieldType === 'dropdown' ? 'selected' : ''; ?>><?= trans("dropdown"); ?></option>
                                        </select>
                                    </div>
                                    <button type="button" class="btn btn-icon flex-shrink-0 btn-light-danger btn-remove-field">
                                        <i class="ki-duotone ki-trash fs-5">
                                            <span class="path1"></span><span class="path2"></span>
                                            <span class="path3"></span><span class="path4"></span><span class="path5"></span>
                                        </i>
                                    </button>
                                </div>
                            <?php endforeach;
                        endif; ?>
                    </div>

                    <div class="fv-row">
                        <button type="button" id="btn-add-custom-field" class="btn btn-sm btn-light-primary">
                            <i class="ki-duotone ki-plus fs-4"></i>
                            <?= trans("add_custom_field"); ?>
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

<?= $this->section('scripts'); ?>
    <script>
        $(document).ready(function () {

            // Handle Registration Type Toggle
            $('#registration_type_select').on('change', function () {
                var selectedType = $(this).val();

                $('.reg-container').hide();
                $('#common_reg_container').hide();

                if (selectedType === 'external') {
                    $('#common_reg_container').fadeIn(200);
                    $('#external_reg_container').fadeIn(200);
                } else if (selectedType === 'internal') {
                    $('#common_reg_container').fadeIn(200);
                    $('#internal_reg_container').fadeIn(200);
                }
            });

            // Init Deadline Date/Time Picker
            if (typeof flatpickr !== 'undefined') {
                $("#inputRegDeadlinePicker").flatpickr({
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    minDate: "today",
                    time_24hr: true,
                    locale: "en"
                });
            }

            // Custom Fields Repeater Logic
            var $fieldsContainer = $('#custom-fields-container');

            $('#btn-add-custom-field').on('click', function () {
                const fieldId = Date.now() + '-' + Math.floor(Math.random() * 1000);
                var newRowHTML = `
                <div class="fv-row d-flex gap-3 align-items-start custom-field-row bg-light p-3 rounded border border-gray-200">
                    <div class="flex-grow-1">
                        <input type="text" name="reg_fields[${fieldId}][label]" class="form-control mb-2" placeholder="<?= trans("field_label", "js"); ?> (e.g., <?= trans("company_name", "js"); ?>)">
                        <input type="text" name="reg_fields[${fieldId}][options]" class="form-control field-options-input" placeholder="<?= trans("comma_separated_options", "js"); ?>" style="display:none;">
                    </div>
                    <div class="w-200px">
                        <select class="form-select custom-field-type-select" name="reg_fields[${fieldId}][type]">
                            <option value="text" selected><?= trans("text_input"); ?></option>
                            <option value="dropdown"><?= trans("dropdown"); ?></option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-icon flex-shrink-0 btn-light-danger btn-remove-field">
                        <i class="ki-duotone ki-trash fs-5">
                            <span class="path1"></span><span class="path2"></span>
                            <span class="path3"></span><span class="path4"></span><span class="path5"></span>
                        </i>
                    </button>
                </div>`;
                $fieldsContainer.append(newRowHTML);
            });

            // Handle Type Change to show/hide options input
            $(document).on('change', '.custom-field-type-select', function () {
                var $optionsInput = $(this).closest('.custom-field-row').find('.field-options-input');
                if ($(this).val() === 'dropdown') {
                    $optionsInput.slideDown(200);
                } else {
                    $optionsInput.slideUp(200).val(''); // Clear on hide
                }
            });

            // Remove Field
            $(document).on('click', '.btn-remove-field', function () {
                Swal.fire(swalOptions(VrConfig.text.confirmDelete)).then((result) => {
                    if (result.isConfirmed) {
                        $(this).closest('.custom-field-row').remove();
                    }
                });
            });

        });
    </script>
<?= $this->endSection(); ?>