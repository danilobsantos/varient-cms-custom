<?= $this->extend($viewsPath . '/layout'); ?>
<?= $this->section('content'); ?>

    <section class="section section-page">
        <div class="container-xl">
            <div class="row">
                <?php if ($page->breadcrumb_active == 1): ?>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= langBaseUrl(); ?>"><?= trans("home"); ?></a></li>
                            <li class="breadcrumb-item active"><?= esc($page->title); ?></li>
                        </ol>
                    </nav>
                <?php endif; ?>
                <h1 class="page-title"><?= esc($page->title); ?></h1>
                <div class="page-content">
                    <div class="row">
                        <div class="col-12 mb-5">
                            <?= $baseSettings->contact_text; ?>
                        </div>
                        <div class="col-12">
                            <h2 class="title-send-message"><?= trans("leave_message"); ?></h2>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <div class="form-contact">

                                <?= loadCommonView('partials/_alert'); ?>

                                <form action="<?= base_url('contact-post'); ?>" method="post" class="needs-validation" novalidate>
                                    <?= csrf_field(); ?>

                                    <div class="mb-3">
                                        <input type="text" class="form-control form-input" name="name" placeholder="<?= trans("name"); ?>" maxlength="199" minlength="1" pattern=".*\S+.*" value="<?= old('name'); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="email" class="form-control form-input" name="email" maxlength="199" placeholder="<?= trans("email"); ?>" value="<?= old('email'); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <textarea class="form-control form-input" name="message" placeholder="<?= trans("message"); ?>" maxlength="4970" minlength="5" required><?= old('message'); ?></textarea>
                                    </div>

                                    <input type="text" name="message_content">

                                    <div class="mb-3">
                                        <div class="form-check form-check-custom">
                                            <input type="checkbox" name="terms_conditions" value="1" id="checkboxTerms" class="form-check-input" required>
                                            <label class="form-check-label" for="checkboxTerms">
                                                <?= trans("terms_conditions_exp"); ?>&nbsp;<a href="<?= !empty($termsConditions) ? esc(langBaseUrl($termsConditions->slug)) : '#'; ?>" class="font-weight-600" target="_blank"><strong><?= trans("terms_conditions"); ?></strong></a>
                                            </label>
                                            <div class="invalid-feedback"><?= trans("must_agree_to_continue"); ?></div>
                                        </div>
                                    </div>

                                    <?= view('common/_captcha', ['captchaContainerClass' => 'mb-4 mt-4 d-flex']); ?>

                                    <div>
                                        <button type="submit" class="btn btn-lg btn-custom"><?= trans("btn_submit"); ?></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <div class="contact-info">
                                <?php if ($baseSettings->contact_phone): ?>
                                    <div class="d-flex justify-content-start align-items-center mb-3">
                                        <div class="contact-icon flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.678.678 0 0 0 .178.643l2.457 2.457a.678.678 0 0 0 .644.178l2.189-.547a1.745 1.745 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.634 18.634 0 0 1-7.01-4.42 18.634 18.634 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877L1.885.511z"/>
                                            </svg>
                                        </div>
                                        <?= esc($baseSettings->contact_phone); ?>
                                    </div>
                                <?php endif;
                                if ($baseSettings->contact_email): ?>
                                    <div class="d-flex justify-content-start align-items-center mb-3">
                                        <div class="contact-icon flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555ZM0 4.697v7.104l5.803-3.558L0 4.697ZM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586l-1.239-.757Zm3.436-.586L16 11.801V4.697l-5.803 3.546Z"/>
                                            </svg>
                                        </div>
                                        <?= esc($baseSettings->contact_email); ?>
                                    </div>
                                <?php endif;
                                if ($baseSettings->contact_address): ?>
                                    <div class="d-flex justify-content-start align-items-center">
                                        <div class="contact-icon flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                                            </svg>
                                        </div>
                                        <?= esc($baseSettings->contact_address); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?= loadCommonView('partials/_map_contact'); ?>

<style>#footer {margin: 0 !important}</style>

<?= $this->endSection(); ?>