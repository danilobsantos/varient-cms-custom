<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

<?php
$selectedLangId = old('lang_id') ?? $poll?->lang_id ?? $activeLang?->id;
$selectedPermission = old('vote_permission') ?: ($poll->vote_permission ?? null) ?: 'all';
$status = old('status') ?? ($poll->status ?? 1);
?>

    <form action="<?= $action ?>" method="post" class="form kt-form d-flex flex-column flex-xl-row gap-5 gap-xl-7 gap-xxl-10 mb-5 mb-xl-7 mb-xxl-10">
        <?= csrf_field(); ?>

        <div class="w-100 flex-xl-row-auto w-xl-300px w-xxl-325px">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?= trans("settings"); ?></h2>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="d-flex flex-column gap-8">

                        <div class="fv-row">
                            <label class="required form-label"><?= trans("language"); ?></label>
                            <select name="lang_id" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <?php foreach ($activeLanguages as $language): ?>
                                    <option value="<?= $language->id; ?>"<?= $selectedLangId == $language->id ? 'selected' : ''; ?>><?= esc($language->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="fv-row">
                            <label class="required form-label"><?= trans("vote_permission"); ?></label>
                            <select name="vote_permission" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <option value="all" <?= $selectedPermission == 'all' ? 'selected' : ''; ?>><?= trans("all_users_can_vote"); ?></option>
                                <option value="registered" <?= $selectedPermission == 'registered' ? 'selected' : ''; ?>><?= trans("registered_users_can_vote"); ?></option>
                            </select>
                        </div>

                        <div class="fv-row mb-7">
                            <div class="d-flex flex-stack">
                                <div class="me-5">
                                    <label class="fs-6 fw-semibold"><?= trans("status"); ?></label>
                                </div>
                                <?= formSwitch('status', (int)$status, ''); ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-row-fluid gap-7 gap-xl-10">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?= trans("general"); ?></h2>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="mb-6 fv-row">
                        <label class="required form-label"><?= trans('question'); ?></label>
                        <textarea name="question" class="form-control mb-2" placeholder="<?= trans('question', 'attr'); ?>" required><?= esc(old('question', $poll->question ?? '')); ?></textarea>
                        <?= validationError('question'); ?>
                    </div>

                    <div class="mb-6 fv-row">
                        <div id="poll-options-container" class="d-flex flex-column gap-4">
                            <?php foreach ($options as $option): ?>
                                <div class="fv-row d-flex gap-4 align-items-center poll-option-item">
                                    <input type="hidden" name="options_id[]" value="<?= esc($option->id ?? ''); ?>">
                                    <input type="text" name="options[]" class="form-control" placeholder="<?= trans('option'); ?>" value="<?= esc($option->option_text ?? ''); ?>">
                                    <button type="button" class="btn btn-sm btn-icon btn-light-danger remove-option-btn">
                                        <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="form-group mt-5">
                            <button type="button" id="add-poll-option-btn" class="btn btn-light-info">
                                <i class="ki-duotone ki-plus fs-4"></i>
                                <?= trans("add_option"); ?>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                    <span class="indicator-label"><?= !empty($poll) ? trans("save_changes") : trans("add_poll"); ?></span>
                    <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            </div>
        </div>
    </form>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
    <script>
        $(document).ready(function () {
            var $optionsContainer = $('#poll-options-container');

            $('#add-poll-option-btn').on('click', function () {
                var newOptionHTML = `
            <div class="fv-row d-flex gap-4 align-items-center poll-option-item">
                <input type="hidden" name="options_id[]" value="">
                <input type="text" name="options[]" class="form-control" placeholder="<?= trans('option'); ?>">
                <button type="button" class="btn btn-sm btn-icon btn-light-danger remove-option-btn">
                    <i class="ki-duotone ki-trash fs-5">
                        <span class="path1"></span><span class="path2"></span>
                        <span class="path3"></span><span class="path4"></span><span class="path5"></span>
                    </i>
                </button>
            </div>`;
                $optionsContainer.append(newOptionHTML);
            });

            $optionsContainer.on('click', '.remove-option-btn', function () {
                $(this).closest('.poll-option-item').remove();
            });
        });
    </script>
<?= $this->endSection(); ?>