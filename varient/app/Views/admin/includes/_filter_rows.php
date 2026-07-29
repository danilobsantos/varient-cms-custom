<select name="show" class="form-select form-select-solid select-filter-rows fw-bold min-w-80px" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>"
        data-allow-clear="false" data-kt-subscription-table-filter="status" data-hide-search="true">
    <option value="20" <?= (!empty(inputGet('show')) && inputGet('show') == 20) || empty(inputGet('show')) ? 'selected' : '' ?>>20</option>
    <option value="40" <?= !empty(inputGet('show')) && inputGet('show') == 40 ? 'selected' : '' ?>>40</option>
    <option value="60" <?= !empty(inputGet('show')) && inputGet('show') == 60 ? 'selected' : '' ?>>60</option>
    <option value="100" <?= !empty(inputGet('show')) && inputGet('show') == 100 ? 'selected' : '' ?>>100</option>
</select>