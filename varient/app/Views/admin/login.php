<?= $this->include('admin/includes/_head'); ?>

<body id="kt_body" class="auth-bg">

<div class="d-flex flex-column flex-root">
    <div class="d-flex flex-column flex-lg-row flex-column-fluid">
        <div class="d-flex flex-column flex-lg-row-auto w-xl-600px bg-primary positon-xl-relative">
            <div class="d-flex flex-column position-xl-fixed top-0 bottom-0 w-xl-600px scroll-y login-sidebar">

                <div class="d-flex flex-row-fluid flex-column flex-center text-center p-10 pt-lg-20">

                    <a href="<?= langBaseUrl(); ?>">
                        <div class="app-icon-wrapper">
                            <img src="<?= getAppIcon(192); ?>" width="64" height="64" class="rounded-1" alt="App Icon">
                        </div>
                    </a>

                    <h1 class="title fw-bold fs-2qx text-white mb-5"><?= trans("welcome"); ?>!</h1>
                    <p class="text-white fw-semibold fs-2">
                        <?= trans("login_admin_exp"); ?>
                    </p>
                </div>

                <div class="d-flex flex-row-auto flex-center">
                    <img src="<?= base_url("assets/admin/media/illustrations/dashboard.png"); ?>" alt="" class="h-200px h-lg-350px mb-10"/>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-lg-row-fluid py-10">
            <div class="d-flex flex-center flex-column flex-column-fluid">
                <div class="w-lg-500px p-10 p-lg-15 mx-auto">
                    <form action="<?= adminUrl("login"); ?>" method="post" class="form kt-form w-100">
                        <?= csrf_field(); ?>

                        <div class="text-center mb-10">
                            <h1 class="text-gray-900 mb-3"><?= trans("log_in"); ?></h1>
                            <div class="text-gray-500 fw-semibold fs-4"><?= trans("log_in_admin_exp"); ?></div>
                        </div>

                        <?= view('admin/includes/_messages'); ?>

                        <div class="fv-row mb-6">
                            <label class="form-label fs-6 fw-bold text-gray-900"><?= trans("email"); ?></label>
                            <input type="email" name="email" class="form-control form-control-lg form-control-solid" autocomplete="off" placeholder="<?= trans("email"); ?>" maxlength="255"/>
                        </div>

                        <div class="fv-row mb-6">
                            <div class="d-flex flex-stack mb-2">
                                <label class="form-label fw-bold text-gray-900 fs-6 mb-0"><?= trans("password"); ?></label>
                            </div>
                            <input type="password" name="password" class="form-control form-control-lg form-control-solid" autocomplete="off" placeholder="<?= trans("password"); ?>" maxlength="255"/>
                        </div>

                        <div class="fv-row mb-6 text-end">
                            <a href="<?= generateURL('forgot_password'); ?>" class="link-primary fs-6 fw-bold"><?= trans("forgot_password"); ?>?</a>
                        </div>

                        <?= view('common/_captcha', ['captchaContainerClass' => 'mb-6 mt-4 d-flex justify-content-center']); ?>

                        <div class="fv-row mb-6 text-end">
                            <button type="submit" class="btn btn-lg btn-primary justify-content-center w-100 mb-5" data-kt-indicator="off">
                                <span class="indicator-label"><?= trans("log_in"); ?></span>
                                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            <div class="d-flex flex-center flex-wrap fs-6 p-5 pb-0">
                <div class="d-flex flex-center align-items-center fw-semibold fs-6">
                    <a href="<?= langBaseUrl(); ?>" class="d-flex align-items-center text-muted text-hover-primary px-2">
                        <?= trans("go_to_homepage"); ?>&nbsp;&nbsp;<i class="bi bi-arrow-right mt-1px"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<?= $this->include('admin/includes/_footer') ?>

</body>
</html>