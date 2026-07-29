<?php
$prepTime = !empty($dataRecipe->prep_time) ? $dataRecipe->prep_time : '';
$cookTime = !empty($dataRecipe->cook_time) ? $dataRecipe->cook_time : '';
$serving = !empty($dataRecipe->serving) ? $dataRecipe->serving : '';
$difficulty = !empty($dataRecipe->difficulty) ? (int)$dataRecipe->difficulty : 0;
?>

<div class="row">
    <div class="col-md-6 col-lg-3 mb-6">
        <label class="form-label"><?= trans('prep_time'); ?>&nbsp;<small>(<?= trans('minutes'); ?>)</small></label>
        <input type="number" class="form-control" name="prep_time" value="<?= esc($prepTime); ?>" min="0" max="999999" placeholder="<?= trans('prep_time'); ?>">
    </div>

    <div class="col-md-6 col-lg-3 mb-6">
        <label class="form-label"><?= trans('cook_time'); ?>&nbsp;<small>(<?= trans('minutes'); ?>)</small></label>
        <input type="number" class="form-control" name="cook_time" value="<?= esc($cookTime); ?>" min="0" max="999999" placeholder="<?= trans('cook_time'); ?>">
    </div>

    <div class="col-md-6 col-lg-3 mb-6">
        <label class="form-label"><?= trans('serving'); ?></label>
        <input type="number" class="form-control" name="serving" value="<?= esc($serving); ?>" min="0" max="999999" placeholder="<?= trans('serving'); ?>">
    </div>

    <div class="col-md-6 col-lg-3 mb-6">
        <label class="form-label"><?= trans('difficulty'); ?></label>
        <select name="difficulty" class="form-select" data-kt-select2="true" data-placeholder="<?= trans("select_an_option", "attr"); ?>" data-allow-clear="false" data-hide-search="true" required>
            <option></option>
            <option value="1" <?= $difficulty === 1 ? 'selected' : ''; ?>><?= trans("easy"); ?></option>
            <option value="2" <?= $difficulty === 2 ? 'selected' : ''; ?>><?= trans("intermediate"); ?></option>
            <option value="3" <?= $difficulty === 3 ? 'selected' : ''; ?>><?= trans("advanced"); ?></option>
        </select>
    </div>
</div>