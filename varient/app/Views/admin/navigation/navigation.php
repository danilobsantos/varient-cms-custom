<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <div class="d-flex flex-column flex-xl-row gap-5 gap-xl-7 gap-xxl-10 mb-5 mb-xl-7 mb-xxl-10">

        <div class="w-100 flex-xl-row-auto w-xl-300px w-xxl-325px">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2><?= trans("settings"); ?></h2>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <form action="<?= base_url("Admin/navigationSettingsPost") ?>" method="post">
                        <?= csrf_field(); ?>
                        <div class="d-flex flex-column gap-8">
                            <div class="fv-row">
                                <label class="required form-label"><?= trans("home_page_link"); ?></label>
                                <select name="show_home_link" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                    <option></option>
                                    <option value="1" <?= $config->show_home_link == 1 ? 'selected' : ''; ?>><?= trans("show"); ?></option>
                                    <option value="0" <?= $config->show_home_link == 0 ? 'selected' : ''; ?>><?= trans("hide"); ?></option>
                                </select>
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

        <div class="d-flex flex-column flex-row-fluid gap-7 gap-xl-10">
            <div class="card card-flush">
                <div class="card-header">
                    <div class="card-title">
                        <h3><?= trans("navigation"); ?></h3>
                    </div>

                    <div class="card-toolbar">
                        <div class="d-flex justify-content-end" data-kt-subscription-table-toolbar="base">
                            <?php if (countItems($activeLanguages) > 1): ?>
                                <div class="w-150px me-3">
                                    <select name="lang_id" class="form-select form-select-solid" data-control="select2" data-hide-search="true" onchange="window.location.href = '<?= adminUrl('navigation?lang='); ?>' + this.value;">
                                        <?php foreach ($activeLanguages as $language): ?>
                                            <option value="<?= $language->id; ?>" <?= $selectedLangId == $language->id ? 'selected' : ''; ?>><?= esc($language->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <a href="<?= adminUrl('navigation/add') ?>" class="btn btn-primary">
                                <i class="ki-duotone ki-plus fs-2"></i>
                                <?= trans("add_menu_link"); ?>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div id="main_navigation_container" class="draggable-zone" data-parent-id="0" data-item-type="none">

                        <?php if ($config->show_home_link == 1): ?>
                            <div class="nav-item-static d-flex align-items-center border rounded-1 p-3 mb-3 py-5">
                                <i class="ki-duotone ki-home fs-3 me-3"></i>
                                <div class="fs-6 fw-semibold flex-grow-1"><?= trans("home"); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($menuLinks)):
                            foreach ($menuLinks as $menuItem):
                                if ($menuItem->location == 'main'):
                                    $subLinks = $menuItem->children;

                                    $itemType = match (true) {
                                        $menuItem->type === "category" => trans("category"),
                                        !empty($menuItem->link) => trans("link"),
                                        default => trans("page"),
                                    }; ?>

                                    <div class="draggable-item" data-item-id="<?= $menuItem->id; ?>" data-item-type="<?= $menuItem->type; ?>" data-have-subs-items="<?= !empty($subLinks) ? '1' : '0'; ?>">

                                        <div class="d-flex align-items-center border rounded-1 px-3 mb-3">
                                            <div class="draggable-handle cursor-move me-4 mt-1">
                                                <i class="bi bi-arrows-move fs-2"></i>
                                            </div>
                                            <div class="fs-6 fw-semibold flex-grow-1 py-5" data-bs-toggle="collapse" href="#collapse_<?= $menuItem->id; ?>" role="button">
                                                <?= esc($menuItem->title); ?>
                                                <em class="text-muted fw-normal ms-2">(<?= $itemType; ?>)</em>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <?php $editUrl = adminUrl('pages/edit/' . $menuItem->id);
                                                if ($menuItem->type === 'category') {
                                                    $editUrl = adminUrl('categories/edit/' . $menuItem->id);
                                                }
                                                if (!empty($menuItem->link)) {
                                                    $editUrl = adminUrl('navigation/edit/' . $menuItem->id);
                                                } ?>

                                                <a href="<?= $editUrl; ?>" target="_blank" class="btn btn-icon btn-sm btn-light-primary">
                                                    <i class="ki-duotone ki-pencil fs-4"><span class="path1"></span><span class="path2"></span></i>
                                                </a>

                                                <?php $deleteUrl = $menuItem->type === 'category' ? base_url('Category/deleteCategory') : base_url('Admin/deletePage'); ?>

                                                <a href="javascript:void(0)" class="btn btn-icon btn-sm btn-light-danger js-action-trigger"
                                                   data-url="<?= $deleteUrl; ?>"
                                                   data-action="delete"
                                                   data-id="<?= $menuItem->id; ?>"
                                                   data-message="<?= trans("confirm_delete", "attr"); ?>"
                                                   data-confirm="1">
                                                    <i class="ki-duotone ki-trash fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                </a>
                                            </div>
                                        </div>

                                        <div class="collapse" id="collapse_<?= $menuItem->id; ?>">
                                            <div class="ps-10 pb-3">
                                                <div class="draggable-zone min-h-100px border-dashed border-2px rounded-1 p-3" data-parent-id="<?= $menuItem->id; ?>" data-item-type="<?= $menuItem->type; ?>">
                                                    <?php if (!empty($subLinks)):
                                                        foreach ($subLinks as $subLink):
                                                            $subItemType = match (true) {
                                                                $menuItem->type === "category" => trans("category"),
                                                                !empty($menuItem->link) => trans("link"),
                                                                default => trans("page"),
                                                            }; ?>

                                                            <div class="draggable-item" data-item-id="<?= $subLink->id; ?>" data-item-type="<?= $subLink->type; ?>" data-have-subs-items="0">

                                                                <div class="d-flex align-items-center border rounded-1 p-3 mb-3">
                                                                    <div class="draggable-handle cursor-move me-4">
                                                                        <i class="bi bi-arrows-move fs-2"></i>
                                                                    </div>
                                                                    <div class="fs-6 fw-semibold flex-grow-1">
                                                                        <?= esc($subLink->title); ?>
                                                                        <em class="text-muted fw-normal ms-2">(<?= $subItemType; ?>)</em>
                                                                    </div>
                                                                    <div class="d-flex gap-2">
                                                                        <?php $subEditUrl = adminUrl('pages/edit/' . $subLink->id);
                                                                        if ($subLink->type === 'category') {
                                                                            $subEditUrl = adminUrl('categories/edit/' . $subLink->id);
                                                                        }
                                                                        if (!empty($subLink->link)) {
                                                                            $subEditUrl = adminUrl('navigation/edit/' . $subLink->id);
                                                                        } ?>

                                                                        <a href="<?= $subEditUrl; ?>" target="_blank" class="btn btn-icon btn-sm btn-light-primary">
                                                                            <i class="ki-duotone ki-pencil fs-4"><span class="path1"></span><span class="path2"></span></i>
                                                                        </a>

                                                                        <?php $subDeleteUrl = $subLink->type === 'category' ? base_url('Category/deleteCategory') : base_url('Admin/deletePage'); ?>

                                                                        <a href="javascript:void(0)" class="btn btn-icon btn-sm btn-light-danger js-action-trigger"
                                                                           data-url="<?= $subDeleteUrl; ?>"
                                                                           data-action="delete"
                                                                           data-id="<?= $subLink->id; ?>"
                                                                           data-message="<?= trans("confirm_delete", "attr"); ?>"
                                                                           data-confirm="1">
                                                                            <i class="ki-duotone ki-trash fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                                        </a>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach;
                                                    else: ?>
                                                        <div class="draggable-empty text-center text-muted"><?= trans('text_drag_sublink_here'); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif;
                            endforeach;
                        endif; ?>
                    </div>
                </div>
            </div>

            <div>
                <div class="alert alert-dismissible bg-light-success border d-flex flex-column flex-sm-row p-5 mb-3">
                    <i class="ki-duotone ki-notification-bing fs-2hx text-success me-4 mb-5 mb-sm-0"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    <div class="d-flex flex-column pe-0 pe-sm-10">
                        <h4 class="fw-semibold"><?= trans("info"); ?></h4>
                        <span><?= trans("navigation_exp"); ?></span>
                    </div>
                </div>

                <div class="alert alert-dismissible bg-light-danger border d-flex flex-column flex-sm-row p-5">
                    <i class="ki-duotone ki-information fs-2tx text-danger me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    <div class="d-flex flex-column pe-0 pe-sm-10">
                        <h4 class="fw-semibold"><?= trans("warning"); ?></h4>
                        <span><?= trans("nav_drag_warning"); ?></span>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>

    <script src="<?= base_url('assets/admin/plugins/draggable/draggable.bundle.js'); ?>"></script>
    <script>
        $(function () {
            const containers = $('.draggable-zone');
            if (!containers.length) {
                return;
            }

            const sortable = new Draggable.Sortable(containers.get(), {
                draggable: '.draggable-item',
                handle: '.draggable-handle',
                mirror: {
                    appendTo: 'body',
                    constrainDimensions: true
                }
            });

            sortable.on('sortable:stop', (event) => {
                const droppedIntoContainer = $(event.data.newContainer);
                const draggedItem = $(event.data.dragEvent.source);
                const parent_type = droppedIntoContainer.data('itemType');
                const item_type = draggedItem.data('itemType');
                const is_item_has_sub_items = draggedItem.data('haveSubsItems');

                if ((parent_type !== 'none' && item_type !== parent_type) || (is_item_has_sub_items == 1 && parent_type !== 'none')) {
                    toastr.error(VrConfig.text.invalid);
                    setTimeout(function () {
                        location.reload();
                    }, 1000);

                } else {
                    setTimeout(updateNavigationOrder, 50);
                }
            });

            function updateNavigationOrder() {
                const menuItems = [];
                $('#main_navigation_container > .draggable-item').each(function (index) {
                    const mainItemEl = $(this);
                    const itemId = mainItemEl.data('itemId');
                    const itemType = mainItemEl.data('itemType');
                    menuItems.push({
                        "item_id": itemId, "item_type": itemType, "parent_id": 0, "new_order": index + 1
                    });
                    mainItemEl.find('.draggable-zone .draggable-item').each(function (subIndex) {
                        const subItemEl = $(this);
                        const subItemId = subItemEl.data('itemId');
                        const subItemType = subItemEl.data('itemType');
                        menuItems.push({
                            "item_id": subItemId, "item_type": subItemType, "parent_id": itemId, "new_order": subIndex + 1
                        });
                    });
                });

                $.ajax({
                    url: generateUrl("Ajax/sortMenuItems"),
                    type: 'POST',
                    data: {'json_menu_items': JSON.stringify(menuItems)}
                });
            }
        });
    </script>

<?= $this->endSection(); ?>