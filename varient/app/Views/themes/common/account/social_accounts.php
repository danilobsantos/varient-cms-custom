<?= $this->extend($viewsPath . '/layout'); ?>
<?= $this->section('content'); ?>

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
                        <p><?= trans("social_accounts_exp"); ?></p>
                    </div>

                    <form action="<?= generateURL('account_settings', 'social_accounts'); ?>" method="post" class="needs-validation" novalidate>
                        <?= csrf_field(); ?>
                        <input type="hidden" name="back_url" value="<?= esc(current_url(true)); ?>">

                        <div class="row g-4 form-section-gap">
                            <?php
                            $allPlatforms = getSocialPlatforms();
                            $allowedKeys = (array)($baseSettings->profile_social_media ?? []);
                            $userSocials = safeDecode(user()->social_media_data);

                            if (!empty($allowedKeys) && is_array($allowedKeys)):
                                foreach ($allowedKeys as $key):
                                    if (!isset($allPlatforms[$key])) continue;

                                    $meta = $allPlatforms[$key];
                                    $userValue = $userSocials->$key ?? '';
                                    $iconTextColor = $meta['text_color'] ?? '#ffffff';
                                    $metaName = trans($key) ?? ''; ?>

                                    <div class="col-md-12 col-lg-6">
                                        <label class="form-label d-flex align-items-center gap-2">
                                            <div class="d-flex align-items-center justify-content-center social-icon" style="background-color: <?= $meta['color']; ?>; color: <?= $iconTextColor; ?>;">
                                                <?= $meta['svg']; ?>
                                            </div>
                                            <?= esc($metaName); ?>
                                        </label>
                                        <input type="text" name="social_media_data[<?= $key; ?>]" value="<?= esc($userValue); ?>" class="form-control form-input" placeholder="https://..." autocomplete="off">
                                    </div>

                                <?php endforeach; ?>
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