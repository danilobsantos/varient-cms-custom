<?php
$badgeId = !empty($badge) ? $badge->id : 0;

$modalId = !empty($badge) ? 'modalEditBadge_' . $badgeId : 'modalAddBadge';

$badgeNameJson = !empty($badge) ? $badge->badge_name : '';
$color = !empty($badge) ? $badge->color : '#9b51e0';
$icon = !empty($badge) ? $badge->icon : '';

?>

<div class="modal fade" id="<?= $modalId; ?>" tabindex="-1" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <form action="<?= adminUrl("user/badges"); ?>" method="post" class="kt-form">
            <?= csrf_field(); ?>

            <?php if (!empty($badgeId)): ?>
                <input type="hidden" name="id" value="<?= esc($badgeId); ?>">
            <?php endif; ?>

            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold"><?= !empty($badge) ? trans("edit_badge") : trans("add_badge"); ?></h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>

                <div class="modal-body py-10 px-lg-17">
                    <div class="fv-row mb-10">
                        <label class="required fs-6 fw-semibold mb-2"><?= trans("name"); ?></label>

                        <?php $uniqueId = !empty($badge->id) ? $badge->id : 'new'; ?>

                        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                            <?php foreach ($activeLanguages as $key => $lang): ?>
                                <li class="nav-item">
                                    <a class="nav-link text-active-primary pb-4 <?= $key === 0 ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#lang_tab_<?= $uniqueId; ?>_<?= $lang->id; ?>" href="javascript:void(0);">
                                        <?= esc($lang->name); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="tab-content">
                            <?php foreach ($activeLanguages as $key => $lang):
                                $badgeName = getLocalizedObjectValue($badgeNameJson, $lang->id, 'name'); ?>
                                <div class="tab-pane fade <?= $key === 0 ? 'show active' : ''; ?>" id="lang_tab_<?= $uniqueId; ?>_<?= $lang->id; ?>" role="tabpanel">
                                    <input type="text" name="name_array[<?= $lang->id; ?>]" value="<?= esc($badgeName); ?>" class="form-control form-control-solid badge-name-input"
                                           placeholder="<?= trans("name", "attr") ?>" data-lang-id="<?= $lang->id; ?>"/>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="fv-row mb-6 fv-row-color-picker">
                        <label class="required form-label"><?= trans('color'); ?></label>
                        <input type="text" class="form-control" id="inputBadgeColor" name="color" maxlength="200" placeholder="<?= trans('color_code', 'attr'); ?>" value="<?= esc($color); ?>" data-coloris required>
                    </div>

                    <div class="fv-row mb-6">
                        <label class="fs-6 fw-semibold mb-4 d-block text-gray-800"><?= trans("icon"); ?></label>

                        <div class="row g-4" data-kt-buttons="true" data-kt-buttons-target="[data-kt-button='true']">

                            <div class="col-2">
                                <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex flex-column flex-center p-4 h-100 <?= empty($icon) ? 'active' : '' ?>" data-kt-button="true">
                                    <input class="btn-check" type="radio" name="icon" value="" <?= empty($icon) ? 'checked' : '' ?> />
                                    <span class="fs-8 fw-bold text-uppercase"><?= trans("none"); ?></span>
                                </label>
                            </div>

                            <?php foreach ($badgeIcons as $key => $svg): ?>
                                <div class="col-2">
                                    <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex flex-column flex-center p-4 h-100 <?= $icon == $key ? 'active' : '' ?>" data-kt-button="true">
                                        <input class="btn-check" type="radio" name="icon" value="<?= $key; ?>" <?= $icon == $key ? 'checked' : '' ?> />

                                        <div class="mb-2 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                                            <?= $svg; ?>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="modal-footer flex-center gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= trans("close"); ?></button>
                    <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                        <span class="indicator-label"><?= !empty($badge) ? trans("save_changes") : trans("add_badge"); ?></span>
                        <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>