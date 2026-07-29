<?= $this->extend('admin/layout'); ?>
<?= $this->section('content'); ?>

    <form action="<?= base_url("Admin/adSpacesPost"); ?>" method="post" class="form d-flex flex-column flex-xl-row kt-form mb-5 mb-xl-7 mb-xxl-10" enctype="multipart/form-data">
        <?= csrf_field(); ?>
        <input type="hidden" name="id" value="<?= $adSpace->id; ?>">

        <div class="col-12">
            <div class="card card-flush py-4">
                <div class="card-header min-h-30px"></div>
                <div class="card-body pt-5">

                    <div class="row fv-row">
                        <div class="col-md-6 mb-6">
                            <label class="required form-label"><?= trans("language"); ?></label>
                            <select name="lang_id" class="form-select" onchange="window.location.href = '<?= adminUrl("ad-spaces"); ?>'+'?lang='+this.value+'&ad_space=<?= strSlug($adSpaceKey); ?>';"
                                    data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <?php foreach ($activeLanguages as $language): ?>
                                    <option value="<?= $language->id; ?>"<?= $langId == $language->id ? 'selected' : ''; ?>><?= esc($language->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-6">
                            <label class="required form-label"><?= trans("select_ad_spaces"); ?></label>
                            <select class="form-select" onchange="window.location.href = '<?= adminUrl("ad-spaces"); ?>'+'?lang=<?= (int)$langId; ?>&ad_space='+this.value;"
                                    data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                <?php foreach ($arrayAdSpaces as $key => $value): ?>
                                    <option value="<?= $key; ?>" <?= $key == $adSpace->ad_space ? 'selected' : ''; ?>><?= esc($value); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <?php if (!empty($arrayAdSpaces[$adSpace->ad_space])): ?>
                        <h4 class="mt-8 mb-6">
                            <?= $arrayAdSpaces[$adSpace->ad_space]; ?>
                            <?php if ($adSpace->ad_space == 'posts_top' || $adSpace->ad_space == 'posts_bottom'): ?>
                                <div class="text-gray-600 fs-7 mt-2"><?= trans("ad_space_posts_exp"); ?></div>
                            <?php endif; ?>
                        </h4>
                    <?php endif; ?>

                    <div class="row bg-opacity-5 bg-gray-600 py-4 px-5 rounded-2 mb-8">
                        <div class="col-md-6 mb-4">
                            <label class="required form-label d-block fw-bold"><?= trans("banner_desktop"); ?> (Tamanho do Bloco)</label>
                            <div class="d-flex align-items-center gap-3 mw-300px">
                                <input type="number" name="desktop_width" class="form-control" value="<?= $adSpace->desktop_width; ?>" min="1" max="5000" placeholder="<?= trans("width"); ?>" required>
                                <span class="text-gray-600">x</span>
                                <input type="number" name="desktop_height" class="form-control" value="<?= $adSpace->desktop_height; ?>" min="1" max="5000" placeholder="<?= trans("height"); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="required form-label d-block fw-bold"><?= trans("banner_mobile"); ?> (Tamanho do Bloco)</label>
                            <div class="d-flex align-items-center gap-3 mw-300px">
                                <input type="number" name="mobile_width" class="form-control" value="<?= $adSpace->mobile_width; ?>" min="1" max="5000" placeholder="<?= trans("width"); ?>" required>
                                <span class="text-gray-600">x</span>
                                <input type="number" name="mobile_height" class="form-control" value="<?= $adSpace->mobile_height; ?>" min="1" max="5000" placeholder="<?= trans("height"); ?>" required>
                            </div>
                        </div>
                    </div>

                    <?php if ($activeTheme->theme != 'classic' && ($adSpace->ad_space == 'sidebar_1' || $adSpace->ad_space == 'sidebar_2')): ?>
                        <div class="fv-row mt-6">
                            <div class="mw-700px">
                                <label class="required form-label"><?= trans("where_to_display"); ?></label>
                                <select name="display_category_id" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" required>
                                    <option></option>
                                    <option value="latest_posts" <?= empty($adSpace->display_category_id) ? 'selected' : ''; ?>><?= trans("latest_posts"); ?></option>
                                    <?php if (!empty($categories)):
                                        foreach ($categories as $category):
                                            if ($category->block_type == 'block-2' || $category->block_type == 'block-3' || $category->block_type == 'block-4'): ?>
                                                <option value="<?= $category->id; ?>" <?= $adSpace->display_category_id == $category->id ? 'selected' : ''; ?>><?= esc($category->name); ?>&nbsp;(<small class="text-gray-600"><?= trans("category"); ?></small>)</option>
                                            <?php endif;
                                        endforeach;
                                    endif; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($adSpace->ad_space == 'in_article_1' || $adSpace->ad_space == 'in_article_2'): ?>
                        <div class="fv-row mt-6">
                            <div class="mw-700px">
                                <label class="required form-label"><?= trans("paragraph"); ?></label>&nbsp;<small class="text-gray-600">(<?= trans("ad_space_paragraph_exp"); ?>)</small>
                                <select name="paragraph_number" class="form-select" data-control="select2" data-hide-search="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>">
                                    <option></option>
                                    <?php for ($i = 1; $i <= 50; $i++): ?>
                                        <option value="<?= $i; ?>" <?= $adSpace->paragraph_number == $i ? 'selected' : ''; ?>><?= $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-end mt-6">
                        <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                            <span class="indicator-label"><?= trans("save_changes"); ?></span>
                            <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>

    <!-- Banners Management Section -->
    <div class="card card-flush py-4 mt-7">
        <div class="card-header">
            <div class="card-title">
                <h2>Banners Rotativos (Máximo 4)</h2>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="row">
                <?php for ($i = 0; $i < 4; $i++): 
                    $banner = $banners[$i] ?? null;
                    $slotNum = $i + 1;
                ?>
                    <div class="col-md-6 mb-6">
                        <div class="card border border-dashed border-gray-400 p-5 rounded-2 bg-light bg-opacity-50">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h4 class="fw-bold m-0">Slot <?= $slotNum; ?></h4>
                                <?php if ($banner): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <!-- Toggle Switch -->
                                        <div class="form-check form-switch form-check-custom form-check-success form-check-solid">
                                            <input class="form-check-input w-30px h-20px toggle-banner-status" type="checkbox" 
                                                   data-id="<?= $banner->id; ?>" <?= $banner->status == 1 ? 'checked' : ''; ?>>
                                        </div>
                                        
                                        <!-- Delete Button -->
                                        <form action="<?= base_url("Admin/deleteAdBannerPost"); ?>" method="post" class="d-inline" onsubmit="return confirm('Deseja realmente excluir este banner? Os arquivos serão apagados.');">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?= $banner->id; ?>">
                                            <button type="submit" class="btn btn-icon btn-sm btn-light-danger" title="<?= trans("delete"); ?>">
                                                <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="badge badge-light-secondary">Vazio</span>
                                <?php endif; ?>
                            </div>

                            <?php if ($banner): ?>
                                <!-- Existing Banner Preview & Info -->
                                <div class="mb-4">
                                    <?php if (!empty($banner->expiry_date)): ?>
                                        <div class="text-gray-600 fs-7 mb-2">
                                            <strong>Expira em:</strong> <?= date('d/m/Y H:i', strtotime($banner->expiry_date)); ?>
                                            <?php if (strtotime($banner->expiry_date) < time()): ?>
                                                <span class="badge badge-light-danger fs-8 ml-2">Expirado</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-gray-600 fs-7 mb-2"><strong>Expira em:</strong> Nunca</div>
                                    <?php endif; ?>

                                    <!-- Previews -->
                                    <div class="d-flex flex-column gap-2 border p-3 rounded bg-white">
                                        <div>
                                            <span class="badge badge-light-info fs-8 mb-1">Desktop Preview:</span>
                                            <div class="ad-preview" style="max-height: 100px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                                <?php if (!empty($banner->ad_code_desktop)): ?>
                                                    <small class="text-muted text-break"><?= esc(substr($banner->ad_code_desktop, 0, 100)) . '...'; ?></small>
                                                <?php elseif (!empty($banner->banner_path_desktop)): ?>
                                                    <img src="<?= getStorageFileUrl($banner->banner_path_desktop, $banner->banner_storage_desktop); ?>" style="max-height: 80px; max-width: 100%; object-fit: contain;">
                                                <?php else: ?>
                                                    <small class="text-muted">Nenhum</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <hr class="my-1">
                                        <div>
                                            <span class="badge badge-light-info fs-8 mb-1">Mobile Preview:</span>
                                            <div class="ad-preview" style="max-height: 100px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                                <?php if (!empty($banner->ad_code_mobile)): ?>
                                                    <small class="text-muted text-break"><?= esc(substr($banner->ad_code_mobile, 0, 100)) . '...'; ?></small>
                                                <?php elseif (!empty($banner->banner_path_mobile)): ?>
                                                    <img src="<?= getStorageFileUrl($banner->banner_path_mobile, $banner->banner_storage_mobile); ?>" style="max-height: 80px; max-width: 100%; object-fit: contain;">
                                                <?php else: ?>
                                                    <small class="text-muted">Nenhum (usará Desktop como fallback)</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Toggle Edit Form collapse -->
                                <button class="btn btn-sm btn-light-primary w-100 mb-2 dropdown-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#editForm_<?= $banner->id; ?>">
                                    Editar Banner
                                </button>
                                
                                <div class="collapse" id="editForm_<?= $banner->id; ?>">
                            <?php else: ?>
                                <!-- Add Banner button and form collapse -->
                                <button class="btn btn-sm btn-primary w-100 mb-2 dropdown-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#addForm_<?= $slotNum; ?>">
                                    + Adicionar Novo Banner
                                </button>
                                
                                <div class="collapse" id="addForm_<?= $slotNum; ?>">
                            <?php endif; ?>

                            <!-- Form contents (for either edit or add) -->
                            <form action="<?= base_url("Admin/saveAdBannerPost"); ?>" method="post" enctype="multipart/form-data" class="mt-4 border-top pt-4">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="id" value="<?= $banner ? $banner->id : ''; ?>">
                                <input type="hidden" name="ad_space_id" value="<?= $adSpace->id; ?>">

                                <!-- Desktop Section -->
                                <div class="bg-gray-100 p-3 rounded mb-4">
                                    <h5 class="fw-bold mb-3 text-primary">Versão Desktop</h5>
                                    <div class="mb-3">
                                        <label class="form-label">Link do Banner (URL)</label>
                                        <input type="text" class="form-control" name="banner_url_desktop" value="<?= $banner ? esc($banner->banner_url_desktop) : ''; ?>" placeholder="https://exemplo.com">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Arquivo do Banner (Desktop)</label>
                                        <input type="file" class="form-control" name="file_ad_code_desktop" accept=".png, .jpg, .jpeg, .gif, .webp">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Código HTML/AdSense Alternativo</label>
                                        <textarea class="form-control min-h-60px" name="ad_code_desktop" placeholder="Cole o código do anúncio aqui..."><?= $banner ? esc($banner->ad_code_desktop) : ''; ?></textarea>
                                    </div>
                                </div>

                                <!-- Mobile Section -->
                                <div class="bg-gray-100 p-3 rounded mb-4">
                                    <h5 class="fw-bold mb-3 text-primary">Versão Mobile</h5>
                                    <div class="mb-3">
                                        <label class="form-label">Link do Banner (URL)</label>
                                        <input type="text" class="form-control" name="banner_url_mobile" value="<?= $banner ? esc($banner->banner_url_mobile) : ''; ?>" placeholder="https://exemplo.com">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Arquivo do Banner (Mobile)</label>
                                        <input type="file" class="form-control" name="file_ad_code_mobile" accept=".png, .jpg, .jpeg, .gif, .webp">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Código HTML/AdSense Alternativo</label>
                                        <textarea class="form-control min-h-60px" name="ad_code_mobile" placeholder="Cole o código do anúncio aqui..."><?= $banner ? esc($banner->ad_code_mobile) : ''; ?></textarea>
                                    </div>
                                </div>

                                <!-- Expiry Date -->
                                <div class="mb-4">
                                    <label class="form-label">Prazo de Exibição (Até qual data/hora)</label>
                                    <input type="datetime-local" class="form-control" name="expiry_date" value="<?= ($banner && $banner->expiry_date) ? date('Y-m-d\TH:i', strtotime($banner->expiry_date)) : ''; ?>">
                                </div>

                                <button type="submit" class="btn btn-sm btn-primary w-100 mt-2">Salvar Banner</button>
                            </form>
                            
                            </div> <!-- end collapse -->
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Toggle Banner Status via AJAX
        document.querySelectorAll('.toggle-banner-status').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                var bannerId = this.getAttribute('data-id');
                var status = this.checked ? 1 : 0;
                
                $.ajax({
                    url: generateUrl('Admin/toggleAdBannerStatusPost'),
                    type: 'POST',
                    data: {
                        id: bannerId,
                        status: status
                    },
                    dataType: 'json',
                    success: function(response) {
                        // Success, status updated
                    },
                    error: function(err) {
                        alert('Erro ao atualizar status do banner');
                    }
                });
            });
        });
    });
    </script>

    <form action="<?= base_url("Admin/adsenseCodePost"); ?>" method="post" class="form d-flex flex-column flex-xl-row kt-form  mt-7">
        <?= csrf_field(); ?>

        <div class="col-md-12 col-lg-6">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2 class="d-flex flex-column">
                            <span><?= trans("adsense_activation_code"); ?></span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-6"><?= trans("custom_footer_codes_exp"); ?></span>
                        </h2>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="mb-6 fv-row">
                        <textarea name="adsense_activation_code" class="form-control min-h-90px" placeholder="<?= trans('adsense_activation_code'); ?>"><?= $config->adsense_activation_code; ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary" data-kt-indicator="off">
                            <span class="indicator-label"><?= trans("save_changes"); ?></span>
                            <span class="indicator-progress"><?= trans("submitting"); ?><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

<?= $this->endSection(); ?>