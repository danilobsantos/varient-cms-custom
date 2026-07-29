<?= $this->section('head'); ?>
    <link href="<?= base_url('assets/admin/plugins/category-selector/category-selector.css'); ?> " rel="stylesheet" type="text/css"/>
<?= $this->endSection(); ?>

    <div id="categoryDropdown"></div>

<?= $this->section('scripts'); ?>
    <script src="<?= base_url('assets/admin/plugins/category-selector/category-selector.js'); ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const categorySelector = new CategorySelector('#categoryDropdown', {
                inputName: '<?= !empty($selectorName) ? $selectorName : "category_id"; ?>',
                extraParams: {
                    lang_id: '<?= $selectorLangId ?? 0; ?>',
                    exclude_id: '<?= $selectorExcludeId ?? 0; ?>'
                },
                initialValue: '<?= $selectorInitialValue ?? 0; ?>',
                initialData: <?= !empty($selectorInitialData) ? $selectorInitialData : 'null'; ?>,
                showId: <?= !empty($showId) ? 1 : 0; ?>,
                allowNone: <?= !empty($selectorAllowNone) ? 1 : 0; ?>,
                urls: {
                    fetch: "<?= adminUrl('category-selector/list'); ?>",
                    search: "<?= adminUrl('category-selector/search'); ?>",
                },
                ux: {
                    placeholder: VrConfig.text.selectCategory,
                    searchPlaceholder: VrConfig.text.search,
                    noResults: VrConfig.text.searchNoResult,
                    loading: VrConfig.text.loading,
                    searching: VrConfig.text.searching,
                    noneOption: VrConfig.text.none,
                }
            });

            $(document).on('change', '#languageSwitcher', function (e) {
                const langId = e.target.value;
                categorySelector.reload({
                    extraParams: {lang_id: langId}
                });
            });

        });
    </script>
<?= $this->endSection(); ?>