<?= $this->include('admin/includes/_head') ?>

    <body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed aside-fixed">

    <script>
        if (localStorage.getItem("sidebar_minimized") === "1") {
            document.body.setAttribute("data-kt-aside-minimize", "on");
        }

        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('kt_aside_toggle');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    setTimeout(function () {
                        const isMinimized = document.body.getAttribute('data-kt-aside-minimize') === 'on';
                        localStorage.setItem("sidebar_minimized", isMinimized ? "1" : "0");
                    }, 50);
                });
            }
        });
    </script>

    <div class="d-flex flex-column flex-root">
        <div class="page d-flex flex-row flex-column-fluid">
            <div id="kt_aside" class="aside" data-kt-drawer="true" data-kt-drawer-name="aside" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_aside_toggle" style="background-color: #0b0c10;">
                <div class="aside-logo flex-column-auto pt-8 pb-7 px-9" id="kt_aside_logo">
                    <a href="<?= adminUrl(); ?>" class="d-flex align-items-center gap-3">
                        <img src="<?= getAppIcon(192); ?>" width="32" height="32" class="rounded-1">
                        <span class="fs-1 app-name"><b><?= esc($baseSettings->application_name); ?></b></span>
                    </a>
                </div>
                <div class="aside-menu flex-column-fluid px-3 px-lg-6">
                    <?= view('admin/includes/_nav_sidebar.php'); ?>
                </div>
            </div>

            <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">

                <div id="kt_header" class="header" data-kt-sticky="true" data-kt-sticky-name="header" data-kt-sticky-offset="{default: '200px', lg: '300px'}">
                    <div class="container-fluid d-flex align-items-stretch justify-content-between" id="kt_header_container">
                        <div class="d-flex align-items-center flex-grow-1">
                            <div class="btn btn-icon btn-circle btn-active-light-primary ms-n2 me-1" id="kt_aside_toggle" data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body" data-kt-toggle-name="aside-minimize">
                                <i class="ki-duotone ki-abstract-14 fs-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                        </div>

                        <div class="d-flex align-items-center flex-shrink-0">

                            <a href="<?= base_url(); ?>" class="btn btn-icon btn-active-light-primary w-35px h-35px w-md-40px h-md-40px" target="_blank">
                                <i class="ki-duotone ki-exit-right-corner fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </a>

                            <div class="d-flex align-items-center ms-2 ms-lg-3">
                                <a href="#" class="btn btn-icon btn-active-light-primary w-35px h-35px w-md-40px h-md-40px" data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                                    <i class="ki-duotone ki-night-day theme-light-show fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                        <span class="path5"></span>
                                        <span class="path6"></span>
                                        <span class="path7"></span>
                                        <span class="path8"></span>
                                        <span class="path9"></span>
                                        <span class="path10"></span>
                                    </i>
                                    <i class="ki-duotone ki-moon theme-dark-show fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </a>

                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px" data-kt-menu="true" data-kt-element="theme-mode-menu">
                                    <div class="menu-item px-3 my-0">
                                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
                                        <span class="menu-icon" data-kt-element="icon">
                                            <i class="ki-duotone ki-night-day fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                                <span class="path5"></span>
                                                <span class="path6"></span>
                                                <span class="path7"></span>
                                                <span class="path8"></span>
                                                <span class="path9"></span>
                                                <span class="path10"></span>
                                            </i>
                                        </span>
                                            <span class="menu-title"><?= trans("light"); ?></span>
                                        </a>
                                    </div>
                                    <div class="menu-item px-3 my-0">
                                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
                                        <span class="menu-icon" data-kt-element="icon">
                                            <i class="ki-duotone ki-moon fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                            <span class="menu-title"><?= trans("dark"); ?></span>
                                        </a>
                                    </div>
                                    <div class="menu-item px-3 my-0">
                                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
                                        <span class="menu-icon" data-kt-element="icon">
                                            <i class="ki-duotone ki-screen fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                            </i>
                                        </span>
                                            <span class="menu-title"><?= trans("system"); ?></span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="app-navbar-item ms-1 ms-lg-3">
                                <div class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-light-primary w-35px h-35px w-md-40px h-md-40px" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                                    <span class="symbol symbol-20px text-muted">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="22" height="22" fill="currentColor">
                                          <path d="M415.9 344L225 344C227.9 408.5 242.2 467.9 262.5 511.4C273.9 535.9 286.2 553.2 297.6 563.8C308.8 574.3 316.5 576 320.5 576C324.5 576 332.2 574.3 343.4 563.8C354.8 553.2 367.1 535.8 378.5 511.4C398.8 467.9 413.1 408.5 416 344zM224.9 296L415.8 296C413 231.5 398.7 172.1 378.4 128.6C367 104.2 354.7 86.8 343.3 76.2C332.1 65.7 324.4 64 320.4 64C316.4 64 308.7 65.7 297.5 76.2C286.1 86.8 273.8 104.2 262.4 128.6C242.1 172.1 227.8 231.5 224.9 296zM176.9 296C180.4 210.4 202.5 130.9 234.8 78.7C142.7 111.3 74.9 195.2 65.5 296L176.9 296zM65.5 344C74.9 444.8 142.7 528.7 234.8 561.3C202.5 509.1 180.4 429.6 176.9 344L65.5 344zM463.9 344C460.4 429.6 438.3 509.1 406 561.3C498.1 528.6 565.9 444.8 575.3 344L463.9 344zM575.3 296C565.9 195.2 498.1 111.3 406 78.7C438.3 130.9 460.4 210.4 463.9 296L575.3 296z"/>
                                        </svg>
                                    </span>
                                </div>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-primary fw-semibold py-4 fs-6 w-175px" data-kt-menu="true" style="">
                                    <?php if (!empty($activeLanguages)):
                                        foreach ($activeLanguages as $language): ?>
                                            <form action="<?= base_url('Admin/setActiveLanguagePost'); ?>" method="post">
                                                <?= csrf_field(); ?>
                                                <div class="menu-item px-3">
                                                    <button type="submit" name="lang_id" value="<?= esc($language->id); ?>" class="menu-link d-flex px-5 w-100 border-0">
                                                        <?= characterLimiter($language->name, 20, '...'); ?>
                                                    </button>
                                                </div>
                                            </form>
                                        <?php endforeach;
                                    endif; ?>
                                </div>
                            </div>


                            <div class="d-flex align-items-center ms-2 ms-lg-3" id="kt_header_user_menu_toggle">
                                <div class="cursor-pointer symbol symbol-circle symbol-35px symbol-md-40px" data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                                    <img alt="avatar" src="<?= getUserAvatar(user()->avatar, user()->storage_avatar); ?>"/>
                                </div>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px" data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <div class="menu-content d-flex align-items-center px-3">
                                            <div class="symbol symbol-50px me-5">
                                                <img alt="avatar" src="<?= getUserAvatar(user()->avatar, user()->storage_avatar); ?>"/>
                                            </div>
                                            <?php $roleName = getLocalizedObjectValue(user()->role_name_data ?? '', $activeLang->id, 'name'); ?>
                                            <div class="d-flex flex-column">
                                                <div class="fw-bold d-flex align-items-center"><?= esc(user()->username); ?>
                                                    <span class="badge badge-light-success fw-bold fs-8 px-2 py-1 ms-2"><?= esc($roleName); ?></span>
                                                </div>
                                                <span class="fw-semibold text-muted fs-7"><?= esc(user()->email); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="separator my-2"></div>

                                    <div class="menu-item px-5">
                                        <a href="<?= generateProfileURL(user()->slug); ?>" class="menu-link px-5"><?= trans("profile"); ?></a>
                                    </div>

                                    <div class="menu-item px-5">
                                        <a href="<?= generateURL('account_settings'); ?>" class="menu-link px-5"><?= trans("account_settings"); ?></a>
                                    </div>
                                    <div class="menu-item px-5">
                                        <a href="<?= generateURL('account_settings', 'change_password'); ?>" class="menu-link px-5"><?= trans("change_password"); ?></a>
                                    </div>
                                    <div class="menu-item px-5">
                                        <form id="logoutForm" action="<?= base_url('auth/logout'); ?>" method="post" style="display:none;">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="ref" value="panel">
                                        </form>

                                        <a href="#" class="menu-link px-5" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
                                            <?= trans('log_out'); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content d-flex flex-column flex-column-fluid fs-6" id="kt_content">
                    <div class="container-fluid" id="kt_content_container">
                        <?php if (empty($isIndexPage)):
                            echo view('admin/includes/_breadcrumb', [
                                    'title' => $title,
                                    'links' => [
                                            ['label' => $title]
                                    ]
                            ]);
                        endif; ?>
                        <?= $this->renderSection('content') ?>
                    </div>
                </div>

                <div class="footer py-4 d-flex flex-lg-column" id="kt_footer">
                    <div class="container-fluid d-flex flex-column flex-md-row flex-stack justify-content-between">
                        <div class="text-gray-900 order-2 order-md-1">
                            <span class="text-gray-500 fw-semibold me-1"><?= esc($baseSettings->copyright); ?></span>
                        </div>
                        <div class="text-gray-900 order-2 order-md-1">
                            <span class="text-gray-500 fw-semibold me-1"><?= trans("version"); ?>&nbsp;<?= VARIENT_VERSION; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php if ($aiWriter->status): ?>
    <?= view("admin/post/_ai_writer"); ?>
<?php endif; ?>

<?= $this->include('admin/includes/_footer') ?>