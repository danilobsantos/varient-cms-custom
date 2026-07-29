<?= $this->extend($viewsPath . '/layout'); ?>
<?= $this->section('content'); ?>

<?php
$emailVerificationEnabled = (int)$config->email_verification === 1;
$emailVerified = (int)$user->email_status === 1;
?>

    <section class="section section-page account-settings">
        <div class="container-xl pb-5">

            <?= loadCommonView('account/_breadcrumb'); ?>

            <div class="row">
                <div class="col-sm-12 col-lg-3 pe-lg-4 mb-5">
                    <?= loadCommonView('account/_sidebar'); ?>
                </div>

                <div class="col-sm-12 col-lg-9">
                    <?= loadCommonView('partials/_alert'); ?>

                    <div class="profile-form-title">
                        <h2><?= esc($title); ?></h2>
                        <p><?= trans("edit_profile_exp"); ?></p>
                    </div>

                    <form action="<?= base_url('account-settings-post'); ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <?= csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= esc($user->id); ?>">
                        <input type="hidden" name="back_url" value="<?= esc(current_url(true)); ?>">

                        <div id="edit_profile_cover" class="edit-profile-cover edit-cover-no-image mb-5 rounded" style="<?= !empty($user->cover_image) ? "background-image: url('" . base_url($user->cover_image) . "');" : ''; ?>">
                            <div class="edit-avatar">
                                <label class="btn btn-custom btn-file-upload">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                        <circle cx="12" cy="13" r="4"></circle>
                                    </svg>
                                    <input type="file" name="file" size="40" accept=".png, .jpg, .webp, .jpeg, .gif" data-img-id="img_preview_avatar" onchange="showImagePreview(this);">
                                </label>
                                <img src="<?= getUserAvatar($user->avatar, $user->storage_avatar); ?>" id="img_preview_avatar" alt="<?= esc($user->username); ?>" class="img-thumbnail" width="180" height="180">
                            </div>

                            <label class="btn btn-custom btn-file-upload btn-edit-cover">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                    <circle cx="12" cy="13" r="4"></circle>
                                </svg>
                                <input type="file" name="image_cover" size="40" accept=".png, .jpg, .webp, .jpeg, .gif" data-img-id="edit_profile_cover" onchange="showImagePreview(this, true);">
                            </label>
                        </div>

                        <p class="mb-5 text-muted small">
                            *<?= trans("warning_edit_profile_image"); ?>
                        </p>

                        <div class="row g-4 form-section-gap">
                            <div class="col-md-6">
                                <label class="form-label"><?= trans("username"); ?></label>
                                <input type="text" name="username" class="form-control form-input" value="<?= esc($user->username); ?>" placeholder="<?= trans("username"); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label"><?= trans("slug"); ?></label>
                                <input type="text" name="slug" class="form-control form-input" value="<?= esc($user->slug); ?>" placeholder="<?= trans("slug"); ?>" required>
                            </div>

                            <div class="col-md-12">
                                <div class="d-flex justify-content-between align-items-end mb-2">
                                    <label class="form-label mb-0"><?= trans("email"); ?></label>
                                    <?php if ($emailVerificationEnabled): ?>
                                        <span class="badge <?= $emailVerified ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?> rounded-pill fw-normal px-2">
                                        <?= trans($emailVerified ? 'verified' : 'unverified'); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <input type="email" name="email" class="form-control form-input" value="<?= esc($user->email); ?>" placeholder="<?= trans("email"); ?>" required>

                                <?php if ($emailVerificationEnabled && !$emailVerified): ?>
                                    <div class="d-flex justify-content-end mt-2">
                                        <button type="submit" name="submit" value="send_verification_email" class="btn btn-link btn-link-verification">
                                            <?= trans('send_verification_email'); ?>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12">
                                <label class="form-label"><?= trans("about_me"); ?></label>
                                <textarea name="about_me" class="form-control form-input form-textarea" rows="4" placeholder="<?= trans("about_me"); ?>"><?= esc($user->about_me); ?></textarea>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="mb-3">
                                <div class="form-check form-switch form-check-success d-flex align-items-center">
                                    <input type="hidden" name="show_email_on_profile" value="0">
                                    <input type="checkbox" name="show_email_on_profile" value="1" class="form-check-input" role="switch" id="swEmailProfile" <?= (int)$user->show_email_on_profile === 1 ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-semibold cursor-pointer" for="swEmailProfile"><?= trans('show_email_on_profile'); ?></label>
                                </div>
                            </div>

                            <?php if ((int)$config->show_rss === 1): ?>
                                <div class="form-check form-switch form-check-success d-flex align-items-center was-validated">
                                    <input type="hidden" name="show_rss_feeds" value="0">
                                    <input type="checkbox" name="show_rss_feeds" value="1" class="form-check-input" role="switch" id="swRssFeeds" <?= (int)$user->show_rss_feeds === 1 ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-semibold cursor-pointer" for="swRssFeeds"><?= trans('rss_feeds'); ?></label>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-end mt-5">
                            <button type="submit" name="submit" value="update" class="btn btn-custom">
                                <?= trans("save_changes") ?>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </section>

<?= $this->endSection(); ?>