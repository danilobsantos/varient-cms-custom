<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$defaultFeedUrl = base_url('service/feed/google-news') . '?lang=' . $config->site_lang;

$status = (int)($config->google_news_settings?->status ?? 0);
$contentType = $config->google_news_settings?->content_type ?? 'content';
$siteName = $config->google_news_settings?->site_name ?? 'My News';
$postLimit = (int)($config->google_news_settings?->post_limit ?? 50);
?>

<div class="d-flex flex-column flex-xl-row gap-5 gap-xl-7 gap-xxl-10 mb-5 mb-xl-7 mb-xxl-10">

    <div class="w-100 flex-xl-row-auto w-xl-300px w-xxl-325px">
        <div class="card card-flush py-4">
            <div class="card-header">
                <div class="card-title">
                    <h2><?= trans("settings"); ?></h2>
                </div>
            </div>
            <div class="card-body pt-5">
                <form action="<?= adminUrl('google-news'); ?>" method="post">
                    <?= csrf_field(); ?>

                    <div class="d-flex flex-column gap-8">
                        <div class="fv-row">
                            <div class="d-flex flex-stack">
                                <div class="me-5">
                                    <label class="fs-6 fw-semibold"><?= trans("status"); ?></label>
                                </div>
                                <?= formSwitch('status', $status, ''); ?>
                            </div>
                        </div>

                        <div class="fv-row">
                            <label class="required form-label"><?= trans("google_news_publication_name"); ?></label>
                            <div class="position-relative">
                                <input type="text" name="site_name" value="<?= esc($siteName); ?>" class="form-control form-control-solid" maxlength="255"/>
                            </div>
                        </div>

                        <div class="fv-row">
                            <label class="required form-label mb-0"><?= trans("rss_content"); ?></label>
                            <div class="text-muted fs-7 mb-3"><?= trans("google_news_rss_content_exp"); ?></div>
                            <select name="content_type" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <option value="content" <?= $contentType === 'content' ? 'selected' : ''; ?>><?= trans("show_full_content"); ?></option>
                                <option value="summary" <?= $contentType === 'summary' ? 'selected' : ''; ?>><?= trans("show_summary_only"); ?></option>
                            </select>
                        </div>

                        <div class="fv-row">
                            <label class="required form-label mb-0"><?= trans("feed_post_limit"); ?></label>
                            <div class="text-muted fs-7 mb-3">
                                <?= trans("feed_post_limit_exp"); ?>
                            </div>
                            <div class="position-relative">
                                <input type="number" name="post_limit" value="<?= $postLimit; ?>" class="form-control form-control-solid" placeholder="50" min="1" max="100"/>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                                <span class="indicator-label"><?= trans("save_changes"); ?></span>
                                <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div class="d-flex flex-column flex-row-fluid gap-5 gap-xl-7 gap-xxl-10">
        <div class="card card-flush py-4">
            <div class="card-header">
                <div class="card-title">
                    <h2><?= trans("feed_link_generator"); ?></h2>
                </div>
            </div>
            <div class="card-body pt-0">

                <div class="fv-row mb-6">
                    <label class="form-label"><?= trans("language"); ?></label>
                    <select name="lang_id" id="languageSwitcher" class="form-select" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="false" data-hide-search="true" required>
                        <option></option>
                        <?php foreach ($activeLanguages as $language): ?>
                            <option value="<?= $language->id; ?>" <?= $activeLang->id == $language->id ? 'selected' : ''; ?>><?= esc($language->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="fv-row mb-6">
                    <label class="form-label"><?= trans("category"); ?></label>
                    <?= view("admin/category/_category_selector", [
                            'selectorName'         => 'category_id',
                            'selectorLangId'       => $activeLang->id ?? 1,
                            'selectorInitialValue' => 0,
                            'selectorInitialData'  => null
                    ]); ?>
                </div>

                <div class="d-flex flex-column mb-5 fv-row mt-5">
                    <label class="form-label"><?= trans("generated_feed_url"); ?></label>
                    <div class="input-group input-group-solid">
                        <span class="input-group-text">
                          <i class="ki-duotone ki-fasten fs-2"><span class="path1"></span><span class="path2"></span></i>
                        </span>

                        <input type="text" class="form-control form-control-solid" id="generated_url" value="<?= esc($defaultFeedUrl); ?>" readonly/>
                        <button type="button" class="btn btn-primary" id="btn_copy_feed"><?= trans("copy"); ?></button>
                    </div>
                </div>

            </div>
        </div>

        <div class="card card-flush py-4">
            <div class="card-header">
                <div class="card-title">
                    <h2><?= trans("integration_endpoints"); ?></h2>
                </div>
            </div>
            <div class="card-body pt-5">

                <p class="text-muted mb-5"><?= trans("integration_endpoints_exp"); ?></p>
                <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6 mb-6">
                    <i class="ki-duotone ki-abstract-26 fs-2tx text-primary me-4">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold w-100"><h4 class="text-gray-900 fw-bold"><?= trans("news_sitemap_for_seo"); ?></h4>
                            <div class="fs-6 text-gray-700">
                                <?= trans("news_sitemap_for_seo_exp"); ?>
                            </div>
                            <div class="d-flex align-items-center flex-wrap gap-2 mt-3">
                                <input type="text" class="form-control form-control-sm bg-gray-100 font-monospace flex-grow-1 w-auto" value="<?= base_url('sitemap-news.xml'); ?>" readonly onclick="this.select();"
                                       style="min-width: 0; max-width: 600px;"> <a href="<?= base_url('sitemap-news.xml'); ?>" target="_blank" class="btn btn-sm btn-light btn-active-primary flex-shrink-0">
                                    <i class="ki-duotone ki-external-link fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                    <?= trans("view"); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6 mb-6">
                    <i class="ki-duotone ki-book-open fs-2tx text-warning me-4">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span>
                    </i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold w-100">
                            <h4 class="text-gray-900 fw-bold"><?= trans("publisher_center_feed_app"); ?></h4>
                            <div class="fs-6 text-gray-700">
                                <?= trans("publisher_center_feed_app_exp"); ?>
                            </div>
                            <div class="d-flex align-items-center flex-wrap gap-2 mt-3">
                                <input type="text" class="form-control form-control-sm bg-gray-100 font-monospace flex-grow-1 w-auto" value="<?= esc($defaultFeedUrl); ?>" readonly onclick="this.select();"
                                       style="min-width: 0; max-width: 600px;">
                                <a href="<?= esc($defaultFeedUrl); ?>" target="_blank" class="btn btn-sm btn-light btn-active-warning flex-shrink-0">
                                    <i class="ki-duotone ki-external-link fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                    <?= trans("view"); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    $(document).ready(function () {
        var $langSelect = $('select[name="lang_id"]');
        var $catInput = $('input[name="category_id"]');
        var $urlInput = $('#generated_url');
        var $copyBtn = $('#btn_copy_feed');

        var baseUrl = "<?= base_url('service/feed/google-news'); ?>";

        function updateUrl() {
            var langId = $langSelect.val();
            var catId = $catInput.val();
            var finalUrl = baseUrl;

            if (catId && catId !== '' && catId !== '0') {
                finalUrl += "/category/" + catId;
            }

            if (langId) {
                finalUrl += "?lang=" + langId;
            }

            $urlInput.val(finalUrl);
        }

        var hiddenElement = $catInput[0];
        if (hiddenElement) {
            var observer = new MutationObserver(function (mutations) {
                updateUrl();
            });
            observer.observe(hiddenElement, {
                attributes: true,
                attributeFilter: ['value']
            });
        }

        $langSelect.on('change', updateUrl);

        $copyBtn.on('click', function (e) {
            e.preventDefault();
            $urlInput.select();
            document.execCommand('copy');

            var originalText = $(this).text();
            $(this).text("Copied!");
            var btn = $(this);
            setTimeout(function () {
                btn.text(originalText);
            }, 2000);
        });
    });
</script>
<?= $this->endSection(); ?>
