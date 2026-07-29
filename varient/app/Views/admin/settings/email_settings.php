<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<div class="d-flex flex-column flex-xl-row gap-5 gap-xl-7 gap-xxl-10 mb-5 mb-xl-7 mb-xxl-10">

    <div class="w-100 flex-xl-row-auto w-xl-300px w-xxl-325px">

        <form action="<?= adminUrl("email-settings"); ?>" method="post" class="kt-form mb-5 mb-xl-7 mb-xxl-10">
            <?= csrf_field(); ?>
            <input type="hidden" name="submit" value="options">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?= trans("options"); ?></h2>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="d-flex flex-column gap-8">
                        <div class="fv-row">
                            <div class="d-flex flex-stack">
                                <div class="me-5">
                                    <label class="fs-6 fw-semibold"><?= trans("email_verification"); ?></label>
                                </div>
                                <?= formSwitch('email_verification', (int)$config->email_verification, ''); ?>
                            </div>
                        </div>

                        <div class="fv-row">
                            <div class="d-flex flex-stack">
                                <div class="me-5">
                                    <label class="fs-6 fw-semibold"><?= trans("send_contact_to_mail"); ?></label>
                                </div>
                                <?= formSwitch('mail_contact_status', (int)$config->mail_contact_status, ''); ?>
                            </div>
                        </div>

                        <div class="fv-row">
                            <label class="required form-label"><?= trans("email"); ?></label>
                            <input type="email" name="mail_contact" class="form-control mb-2" placeholder="<?= trans("email", "attr"); ?>" value="<?= esc($config->mail_contact); ?>">
                            <div class="text-muted fs-7 mb-7"><?= trans("contact_messages_will_send"); ?></div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                                <span class="indicator-label"><?= trans("save_changes"); ?></span>
                                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </form>

        <form action="<?= adminUrl("email-settings"); ?>" method="post" class="kt-form">
            <?= csrf_field(); ?>
            <input type="hidden" name="submit" value="test_email">

            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?= trans("send_test_email"); ?></h2>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="d-flex flex-column gap-8">
                        <span class="text-muted"><?= trans('send_test_email_exp'); ?></span>
                        <div class="fv-row">
                            <label class="required form-label"><?= trans("email"); ?></label>
                            <input type="email" name="email" class="form-control mb-2" placeholder="<?= trans("email", "attr"); ?>" required>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                                <span class="indicator-label"><?= trans("send_email"); ?></span>
                                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>

    <form action="<?= adminUrl("email-settings"); ?>" method="post" class="kt-form d-flex flex-column flex-row-fluid gap-5 gap-xl-7 gap-xxl-10">
        <?= csrf_field(); ?>
        <input type="hidden" name="submit" value="settings">

        <div class="card card-flush py-4">
            <div class="card-header">
                <div class="card-title">
                    <h2><?= trans("settings"); ?></h2>
                </div>
            </div>

            <div class="card-body pt-5">

                <div class="mb-6 fv-row">
                    <label class="required form-label"><?= trans("mail_service"); ?></label>
                    <select name="mail_service" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>"
                            onchange="window.location.href = '<?= adminUrl('email-settings'); ?>?service='+this.value+'&protocol=<?= esc($protocol); ?>';">
                        <?php foreach ($emailServices as $key => $label): ?>
                            <option value="<?= esc($key); ?>" <?= $service == $key ? 'selected' : ''; ?>>
                                <?= esc($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($service == 'brevo'): ?>
                    <div class="mb-6 fv-row">
                        <label class="form-label"><?= trans('api_key'); ?></label>
                        <input type="password" name="brevo_api_key" class="form-control mb-2" placeholder="<?= trans("api_key", "attr"); ?>" value="<?= esc($emailSettings->brevo_api_key); ?>">
                    </div>
                <?php elseif ($service == 'mailgun'): ?>
                    <div class="mb-6 fv-row">
                        <label class="form-label"><?= trans('api_key'); ?></label>
                        <input type="password" name="mailgun_api_key" class="form-control mb-2" placeholder="<?= trans("api_key", "attr"); ?>" value="<?= esc($emailSettings->mailgun_api_key); ?>">
                    </div>

                    <div class="mb-6 fv-row">
                        <label class="required form-label"><?= trans("region"); ?></label>
                        <select name="mailgun_region" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                            <option value="us" <?= $emailSettings->mailgun_region == 'us' ? "selected" : ""; ?>>US</option>
                            <option value="eu" <?= $emailSettings->mailgun_region == 'eu' ? "selected" : ""; ?>>EU</option>
                        </select>
                    </div>

                    <div class="mb-6 fv-row">
                        <label class="form-label"><?= trans('domain'); ?></label>
                        <input type="text" name="mailgun_domain" class="form-control mb-2" placeholder="e.g. mg.example.com" value="<?= esc($emailSettings->mailgun_domain); ?>">
                    </div>

                    <div class="mb-6 fv-row">
                        <label class="form-label"><?= trans('sender_email_address'); ?></label>
                        <input type="text" name="mailgun_sender_email" class="form-control mb-2" placeholder="e.g. info@mg.example.com" value="<?= esc($emailSettings->mailgun_sender_email); ?>">
                    </div>
                <?php else: ?>
                    <div class="mb-6 fv-row">
                        <label class="required form-label"><?= trans("mail_protocol"); ?></label>
                        <select name="mail_protocol" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>"
                                onchange="window.location.href = '<?= adminUrl('email-settings'); ?>?service=<?= esc($service); ?>&protocol='+this.value;">
                            <option value="smtp" <?= $protocol == 'smtp' ? "selected" : ""; ?>><?= trans('smtp'); ?></option>
                            <option value="mail" <?= $protocol == 'mail' ? "selected" : ""; ?>><?= trans('mail'); ?></option>
                        </select>
                    </div>

                    <?php if ($protocol == 'smtp'): ?>
                        <div class="mb-6 fv-row">
                            <label class="required form-label"><?= trans("encryption"); ?></label>
                            <select name="mail_encryption" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <option value="tls" <?= $emailSettings->mail_encryption == "tls" ? "selected" : ""; ?>>TLS</option>
                                <option value="ssl" <?= $emailSettings->mail_encryption == "ssl" ? "selected" : ""; ?>>SSL</option>
                            </select>
                        </div>

                        <div class="mb-6 fv-row">
                            <label class="form-label"><?= trans('mail_host'); ?></label>
                            <input type="text" name="mail_host" class="form-control mb-2" placeholder="<?= trans('mail_host'); ?>" value="<?= esc($emailSettings->mail_host); ?>">
                        </div>

                        <div class="mb-6 fv-row">
                            <label class="form-label"><?= trans('mail_port'); ?></label>
                            <input type="text" name="mail_port" class="form-control mb-2" placeholder="<?= trans('mail_port'); ?>" value="<?= esc($emailSettings->mail_port); ?>">
                        </div>

                        <div class="mb-6 fv-row">
                            <label class="form-label"><?= trans('mail_username'); ?></label>
                            <input type="text" name="mail_username" class="form-control mb-2" placeholder="<?= trans('mail_username'); ?>" value="<?= esc($emailSettings->mail_username); ?>">
                        </div>

                        <div class="mb-6 fv-row">
                            <label class="form-label"><?= trans('mail_password'); ?></label>
                            <input type="password" class="form-control" name="mail_password" placeholder="<?= trans('mail_password'); ?>" value="<?= esc($emailSettings->mail_password); ?>">
                        </div>
                    <?php endif;
                endif; ?>

                <div class="mb-6 fv-row">
                    <label class="form-label"><?= trans('mail_title'); ?></label>
                    <input type="text" class="form-control" name="mail_title" placeholder="<?= trans('mail_title'); ?>" value="<?= esc($emailSettings->mail_title); ?>">
                </div>

                <div class="mb-6 fv-row">
                    <label class="form-label"><?= trans('reply_to'); ?></label>
                    <input type="email" class="form-control" name="mail_reply_to" placeholder="<?= trans('reply_to'); ?>" value="<?= esc($emailSettings->mail_reply_to); ?>">
                </div>
            </div>
        </div>

        <div class="card card-flush py-4">
            <div class="card-header">
                <div class="card-title">
                    <h2><?= trans("email_templates"); ?></h2>
                </div>
            </div>

            <div class="card-body pt-5">
                <input type="hidden" id="selected_theme_input" name="email_template" value="<?= esc($template); ?>">

                <div class="row row-cols-2 row-cols-lg-3 row-cols-xl-3 row-cols-xxl-4 row-cols-xxxl-5 g-4 email-templates">

                    <?php $templates = getAppDefault('emailTemplates');
                    foreach ($templates as $key => $value):?>
                        <div class="col">
                            <label class="theme-card-wrapper">
                                <input type="radio" name="theme_selection" value="<?= $key; ?>" class="theme-radio" onchange="updateHiddenInput(this)" <?= $key == $template ? 'checked' : ''; ?>>
                                <div class="theme-card">
                                    <div class="active-badge"><i class="bi bi-check-lg"></i></div>
                                    <div class="theme-img-container">
                                        <img src="<?= base_url("assets/admin/media/email-templates/" . $key . ".svg"); ?>" alt="" class="theme-img">
                                        <div class="theme-overlay">
                                            <button type="button" class="btn btn-light btn-sm fw-bold shadow-sm rounded-pill px-3" onclick="event.preventDefault(); openPreview('<?= $key; ?>')"><?= trans("preview"); ?></button>
                                            <div class="btn btn-sm btn-primary fw-bold shadow-sm rounded-pill px-3 ms-2"><?= trans("select"); ?></div>
                                        </div>
                                    </div>
                                    <div class="card-body-custom">
                                        <span class="fw-bolder fs-7"><?= $value; ?></span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    <?php endforeach; ?>

                </div>


                <div class="modal fade" id="previewModal" tabindex="-1">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title" id="previewModalLabel"></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body position-relative">

                                <div id="previewLoading"
                                     class="position-absolute top-50 start-50 translate-middle">
                                    Loading...
                                </div>

                                <iframe id="emailPreviewFrame" class="w-100 border-0" style="height:700px;"></iframe>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                <span class="indicator-label"><?= trans("save_changes"); ?></span>
                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>
        </div>
    </form>

</div>

<?= $this->endSection(); ?>


<?= $this->section('scripts'); ?>
<script>
    function updateHiddenInput(radio) {
        document.getElementById('selected_theme_input').value = radio.value;
    }

    function openPreview(themeValue) {

        const previewModalEl = document.getElementById('previewModal');
        const previewModal = new bootstrap.Modal(previewModalEl);
        previewModal.show();

        const loader = document.getElementById('previewLoading');
        loader.style.display = 'block';

        $.ajax({
            url: generateUrl('Ajax/loadEmailTemplatePreview'),
            type: 'POST',
            data: {template: themeValue},
            dataType: 'json',
            success: function (response) {
                if (response.status !== 1) return;

                const iframe = document.getElementById('emailPreviewFrame');
                if (!iframe) {
                    console.error('iframe not found');
                    return;
                }

                const doc = iframe.contentDocument || iframe.contentWindow.document;

                doc.open();
                doc.write(response.htmlContent);
                doc.close();

                loader.style.display = 'none';

                const title = themeValue
                    .split('_')
                    .map(w => w.charAt(0).toUpperCase() + w.slice(1))
                    .join(' ');

                document.getElementById('previewModalLabel').innerText = title;
            },
            error: function () {
                loader.style.display = 'none';
            }
        });
    }

    function confirmThemeSelection() {
        const radios = document.getElementsByName('theme_selection');
        const currentPreviewTheme = document.getElementById('previewModalLabel').innerText.toLowerCase().replace(/ /g, '_');

        const modalEl = document.getElementById('previewModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();
    }

    function saveSelection() {
        const selected = document.querySelector('input[name="theme_selection"]:checked');
        if (selected) {
            alert('Saving theme: ' + selected.value);
        }
    }
</script>
<?= $this->endSection(); ?>
