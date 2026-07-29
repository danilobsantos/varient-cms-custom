<?php $activeContentTab = inputGet('content_tab') ?? 'main_slider'; ?>

    <div class="card card-flush py-4">
        <div class="card-header">
            <div class="card-title">
                <h2><?= trans("featured_content_settings"); ?></h2>
            </div>
        </div>
        <div class="card-body pt-5">
            <form action="<?= adminUrl('content-settings'); ?>" method="post" class="form">
                <?= csrf_field(); ?>
                <input type="hidden" name="submit" value="featured_content">
                <input type="hidden" name="content_tab" value="<?= esc($activeContentTab); ?>" id="selectedContentSettingsTab">

                <ul class="nav nav-pills nav-pills-featured mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center gap-3 fw-semibold py-3 <?= $activeContentTab == 'main_slider' ? 'active' : ''; ?>" data-bs-toggle="pill" data-bs-target="#tab_main_slider" data-value="main_slider"
                                type="button" role="tab" aria-controls="pills-profile" aria-selected="false">
                            <i class="ki-duotone ki-setting-4 fs-3"></i><?= trans("main_slider"); ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center gap-3 fw-semibold py-3 <?= $activeContentTab == 'featured_posts' ? 'active' : ''; ?>" data-bs-toggle="pill" data-bs-target="#tab_featured_posts" data-value="featured_posts"
                                type="button" role="tab" aria-controls="pills-home" aria-selected="true">
                            <i class="ki-duotone ki-star fs-3"></i><?= trans("featured_posts"); ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center gap-3 fw-semibold py-3 <?= $activeContentTab == 'recommended_posts' ? 'active' : ''; ?>" data-bs-toggle="pill" data-bs-target="#tab_recommended_posts" data-value="recommended_posts"
                                type="button" role="tab" aria-controls="pills-contact" aria-selected="false">
                            <i class="ki-duotone ki-abstract-28 fs-3"><span class="path1"></span><span class="path2"></span></i><?= trans("recommended_posts"); ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center gap-3 fw-semibold py-3 <?= $activeContentTab == 'breaking_news' ? 'active' : ''; ?>" data-bs-toggle="pill" data-bs-target="#tab_breaking_news" data-value="breaking_news"
                                type="button" role="tab" aria-controls="pills-contact" aria-selected="false">
                            <i class="ki-duotone ki-abstract-22 fs-3"><span class="path1"></span><span class="path2"></span></i><?= trans("breaking_news"); ?>
                        </button>
                    </li>
                </ul>

                <div class="tab-content pt-6">
                    <div class="tab-pane fade <?= $activeContentTab == 'main_slider' ? 'show active' : ''; ?>" id="tab_main_slider" role="tabpanel" tabindex="0">
                        <div class="fv-row mb-6">
                            <label class="form-label"><?= trans("content_source"); ?></label>
                            <select name="slider_source" class="form-select slider-content-source" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <option value="manual" <?= $config->featured_content_settings->slider_source == 'manual' ? 'selected' : ''; ?>><?= trans("manual_selection_only"); ?></option>
                                <option value="latest" <?= $config->featured_content_settings->slider_source == 'latest' ? 'selected' : ''; ?>><?= trans("latest_posts"); ?>&nbsp;(<?= trans("newest_first"); ?>)</option>
                            </select>
                        </div>

                        <div class="fv-row mb-6 slider-sorting-logic">
                            <label class="form-label"><?= trans("sorting_logic"); ?></label>
                            <select name="slider_sorting" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <option value="slider_order" <?= $config->featured_content_settings->slider_sorting == 'slider_order' ? 'selected' : ''; ?>><?= trans("slider_order"); ?></option>
                                <option value="by_date" <?= $config->featured_content_settings->slider_sorting == 'by_date' ? 'selected' : ''; ?>><?= trans("publication_date"); ?>&nbsp;(<?= trans("newest_first"); ?>)</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-lg-6">
                                <div class="fv-row mb-6 slider-display-duration">
                                    <label class="form-label"><?= trans("display_duration"); ?>&nbsp;(<?= trans("number_of_days"); ?>)</label>
                                    <input type="number" name="slider_duration" value="<?= esc($config->featured_content_settings->slider_duration ?? 10); ?>" class="form-control" placeholder="<?= trans("number_of_days", "attr") ?>" min="0" max="5000">
                                </div>
                            </div>
                            <div class="col-sm-12 col-lg-6">
                                <div class="fv-row mb-6">
                                    <label class="form-label"><?= trans("display_limit"); ?></label>
                                    <input type="number" name="slider_limit" value="<?= esc($config->featured_content_settings->slider_limit ?? 15); ?>" class="form-control" placeholder="<?= trans("display_limit", "attr") ?>" min="0" max="5000">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade <?= $activeContentTab == 'featured_posts' ? 'show active' : ''; ?>" id="tab_featured_posts" role="tabpanel" tabindex="0">

                        <div class="fv-row mb-6">
                            <div class="d-flex align-items-center gap-6">
                                <label class="form-label mb-0"><?= trans("exclude_slider_posts"); ?></label>
                                <?= formSwitch('exclude_slider_posts', (int)($config->featured_content_settings->exclude_slider_posts ?? 0), trans("yes")); ?>
                            </div>
                            <div class="text-gray-500 mt-1 fw-semibold fs-6"><?= trans("exclude_slider_posts_exp"); ?></div>
                        </div>

                        <div class="fv-row mb-6">
                            <label class="form-label"><?= trans("content_source"); ?></label>
                            <select name="featured_source" class="form-select featured-content-source" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <option value="manual" <?= $config->featured_content_settings->featured_source == 'manual' ? 'selected' : ''; ?>><?= trans("manual_selection_only"); ?></option>
                                <option value="latest" <?= $config->featured_content_settings->featured_source == 'latest' ? 'selected' : ''; ?>><?= trans("latest_posts"); ?>&nbsp;(<?= trans("newest_first"); ?>)</option>
                            </select>
                        </div>

                        <div class="fv-row mb-6 featured-sorting-logic">
                            <label class="form-label"><?= trans("sorting_logic"); ?></label>
                            <select name="featured_sorting" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <option value="featured_order" <?= $config->featured_content_settings->featured_sorting == 'featured_order' ? 'selected' : ''; ?>><?= trans("featured_order"); ?></option>
                                <option value="by_date" <?= $config->featured_content_settings->featured_sorting == 'by_date' ? 'selected' : ''; ?>><?= trans("publication_date"); ?>&nbsp;(<?= trans("newest_first"); ?>)</option>
                            </select>
                        </div>

                        <div class="fv-row mb-6 featured-display-duration">
                            <label class="form-label"><?= trans("display_duration"); ?>&nbsp;(<?= trans("number_of_days"); ?>)</label>
                            <input type="number" name="featured_duration" value="<?= esc($config->featured_content_settings->featured_duration ?? 10); ?>" class="form-control" placeholder="<?= trans("number_of_days", "attr") ?>" min="0" max="5000">
                        </div>
                    </div>

                    <div class="tab-pane fade <?= $activeContentTab == 'recommended_posts' ? 'show active' : ''; ?>" id="tab_recommended_posts" role="tabpanel" aria-labelledby="pills-contact-tab" tabindex="0">
                        <div class="fv-row mb-6">
                            <label class="form-label"><?= trans("sorting_logic"); ?></label>
                            <select name="recommended_sorting" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <option value="by_date" <?= $config->featured_content_settings->recommended_sorting == 'by_date' ? 'selected' : ''; ?>><?= trans("publication_date"); ?>&nbsp;(<?= trans("newest_first"); ?>)</option>
                                <option value="random" <?= $config->featured_content_settings->recommended_sorting == 'random' ? 'selected' : ''; ?>><?= trans("randomly"); ?></option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-lg-6">
                                <div class="fv-row mb-6 display-duration">
                                    <label class="form-label"><?= trans("display_duration"); ?>&nbsp;(<?= trans("number_of_days"); ?>)</label>
                                    <input type="number" name="recommended_duration" value="<?= esc($config->featured_content_settings->recommended_duration ?? 20); ?>" class="form-control" placeholder="<?= trans("number_of_days", "attr") ?>" min="0" max="5000">
                                </div>
                            </div>
                            <div class="col-sm-12 col-lg-6">
                                <div class="fv-row mb-6">
                                    <label class="form-label"><?= trans("display_limit"); ?></label>
                                    <input type="number" name="recommended_limit" value="<?= esc($config->featured_content_settings->recommended_limit ?? 5); ?>" class="form-control" placeholder="<?= trans("display_limit", "attr") ?>" min="0" max="5000">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade  <?= $activeContentTab == 'breaking_news' ? 'show active' : ''; ?>" id="tab_breaking_news" role="tabpanel" aria-labelledby="pills-contact-tab" tabindex="0">

                        <div class="fv-row d-flex align-items-center gap-6 mb-6">
                            <label class="form-label mb-0"><?= trans("status"); ?></label>
                            <?= formSwitch('br_news_status', (int)($config->featured_content_settings->br_news_status ?? 1), trans("enable")); ?>
                        </div>

                        <div class="fv-row mb-6">
                            <label class="form-label"><?= trans("content_source"); ?></label>
                            <select name="br_news_source" class="form-select breaking-news-content-source" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <option value="manual" <?= $config->featured_content_settings->br_news_source == 'manual' ? 'selected' : ''; ?>><?= trans("manual_selection_only"); ?></option>
                                <option value="latest" <?= $config->featured_content_settings->br_news_source == 'latest' ? 'selected' : ''; ?>><?= trans("latest_posts"); ?>&nbsp;(<?= trans("newest_first"); ?>)</option>
                            </select>
                        </div>

                        <div class="fv-row mb-6 breaking-news-sorting-logic">
                            <label class="form-label"><?= trans("sorting_logic"); ?></label>
                            <select name="br_news_sorting" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <option value="by_date" <?= $config->featured_content_settings->br_news_sorting == 'by_date' ? 'selected' : ''; ?>><?= trans("publication_date"); ?>&nbsp;(<?= trans("newest_first"); ?>)</option>
                                <option value="random" <?= $config->featured_content_settings->br_news_sorting == 'random' ? 'selected' : ''; ?>><?= trans("randomly"); ?></option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-lg-6">
                                <div class="fv-row mb-6 breaking-news-display-duration">
                                    <label class="form-label"><?= trans("display_duration"); ?>&nbsp;(<?= trans("number_of_days"); ?>)</label>
                                    <input type="number" name="br_news_duration" value="<?= esc($config->featured_content_settings->br_news_duration ?? 5); ?>" class="form-control" placeholder="<?= trans("number_of_days", "attr") ?>" min="0" max="5000">
                                </div>
                            </div>
                            <div class="col-sm-12 col-lg-6">
                                <div class="fv-row mb-6">
                                    <label class="form-label"><?= trans("display_limit"); ?></label>
                                    <input type="number" name="br_news_limit" value="<?= esc($config->featured_content_settings->br_news_limit ?? 15); ?>" class="form-control" placeholder="<?= trans("display_limit", "attr") ?>" min="0" max="5000">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                        <span class="indicator-label"><?= trans("save_changes"); ?></span>
                        <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </form>

        </div>
    </div>

<?= $this->section('scripts'); ?>
    <script>
        // Set active tab for featured content settings
        document.querySelectorAll('.nav-pills-featured [data-bs-toggle="pill"]').forEach(button => {
            button.addEventListener('shown.bs.tab', function () {
                document.getElementById('selectedContentSettingsTab').value = this.dataset.value;
            });
        });

    </script>
<?= $this->endSection(); ?>