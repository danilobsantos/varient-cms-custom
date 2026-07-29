<?php
if (!empty($categories)):
    foreach ($categories as $item):
        $hasChildren = $item->has_children ?? false;
        $parentName = $item->parent_name ?? null;
        $isSearch = $isSearchMode ?? false;
        $canExpand = (!$isSearch && $hasChildren);
        $wrapperId = 'wrapper-' . $item->id;
        $clickClass = $canExpand ? 'category-trigger category-name cursor-pointer w-100' : 'category-name cursor-default';
        $loadAttr = $canExpand ? 'data-loaded="false" data-id="' . $item->id . '"' : ''; ?>

        <div class="category-wrapper" id="<?= $wrapperId ?>">
            <div class="list-group-item d-flex align-items-center border rounded-1 px-3 mb-3">
                <div class="col-content flex-grow-1 d-flex align-items-center">
                    <div class="<?= $clickClass ?> d-flex align-items-center" <?= $loadAttr ?>>
                        <?php if ($isSearch): ?>
                            <i class="ki-solid ki-magnifier me-3 text-gray-500"></i>
                        <?php elseif ($hasChildren): ?>
                            <div class="d-flex align-items-center justify-content-center text-center bg-gray-100 w-30px h-30px me-2 rounded rounded-1 bg-hover-light-primary">
                                <i class="ki-duotone ki-right arrow-icon text-gray-500 fs-3 transition-transform m-0"></i>
                            </div>
                        <?php else: ?>
                            <span class="me-2 d-inline-block text-center" style="width: 15px;">
                                <i class="bi bi-dot fs-2 text-gray-500"></i>
                            </span>
                        <?php endif; ?>
                        <div class="d-flex flex-column">
                            <?php if ($isSearch && $parentName): ?>
                                <span class="text-gray-500 fs-9 mb-1">
                                  <i class="bi bi-arrow-90deg-right me-1 fs-9"></i><?= esc($parentName) ?>
                                </span>
                            <?php endif; ?>
                            <span class="fw-bold text-gray-800 fs-6 hover-primary"><?= esc($item->name) ?>&nbsp;<smal
                                        class="text-gray-400">(<?= trans("id"); ?>:&nbsp;<?= $item->id; ?>)</smal></span>
                        </div>
                        <div class="spinner-border spinner-border-sm text-primary ms-3 loading-spinner" role="status"
                             style="display: none;"></div>
                    </div>
                </div>

                <div class="d-flex gap-4">

                    <?php if ($premiumMembership->status): ?>
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($premiumMembership->exclusiveSaleStatus && (int)$item->is_exclusive === 1): ?>
                                <span class="badge badge-success"><?= trans('exclusive'); ?></span>
                            <?php else:
                                if ($premiumMembership->subscriptionMode === 'selective' && (int)$item->is_premium === 1): ?>
                                    <span class="badge badge-primary"><?= trans('premium'); ?></span>
                                <?php endif;
                            endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex align-items-center gap-2">
                        <?php if ((int)$item->status === 1): ?>
                            <span class="badge badge-light-success"><?= trans('active'); ?></span>
                        <?php else: ?>
                            <span class="badge badge-light-danger"><?= trans('inactive'); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= adminUrl('categories/edit/' . esc($item->id)); ?>"
                           class="btn btn-icon btn-sm btn-light-primary">
                            <i class="ki-duotone ki-pencil fs-4"><span class="path1"></span><span class="path2"></span></i>
                        </a>

                        <button type="button" class="btn btn-icon btn-sm btn-light-danger js-action-trigger"
                                data-url="<?= base_url('Category/deleteCategory'); ?>"
                                data-action="delete"
                                data-id="<?= $item->id; ?>"
                                data-message="<?= trans("confirm_delete", "attr"); ?>"
                                data-confirm="1"
                                data-target="#<?= $wrapperId; ?>">
                            <i class="ki-duotone ki-trash fs-4"><span class="path1"></span><span
                                        class="path2"></span><span class="path3"></span><span class="path4"></span><span
                                        class="path5"></span></i>
                        </button>
                    </div>
                </div>

            </div>

            <div id="children-<?= $item->id ?>" class="sub-category-container ps-5 ms-4 border-start border-gray-200"
                 style="display: none;"></div>
        </div>
    <?php endforeach;
endif; ?>