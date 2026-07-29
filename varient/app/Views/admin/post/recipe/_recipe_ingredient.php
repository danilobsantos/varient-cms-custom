<div class="row g-7 g-xl-10">

    <div class="col-12">
        <div class="card card-flush">
            <div class="card-header collapsible cursor-pointer rotate <?= !empty($post) ? 'collapsed' : 'active'; ?>" data-bs-toggle="collapse" data-bs-target="#ingredients_card_collapsible">
                <h3 class="card-title fw-bold fs-3 gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="22" height="22" fill="#78829D">
                        <path d="M240 96C284.2 96 320 131.8 320 176L320 192C320 200.8 312.8 208 304 208C259.8 208 224 172.2 224 128L224 112C224 103.2 231.2 96 240 96zM120 128L168 128C181.3 128 192 138.7 192 152C192 165.3 181.3 176 168 176L120 176C106.7 176 96 165.3 96 152C96 138.7 106.7 128 120 128zM88 200L200 200C213.3 200 224 210.7 224 224C224 237.3 213.3 248 200 248L88 248C74.7 248 64 237.3 64 224C64 210.7 74.7 200 88 200zM96 296C96 282.7 106.7 272 120 272L168 272C181.3 272 192 282.7 192 296C192 309.3 181.3 320 168 320L120 320C106.7 320 96 309.3 96 296zM336 112C336 103.2 343.2 96 352 96C396.2 96 432 131.8 432 176L432 192C432 200.8 424.8 208 416 208C371.8 208 336 172.2 336 128L336 112zM464 96C508.2 96 544 131.8 544 176L544 192C544 200.8 536.8 208 528 208C483.8 208 448 172.2 448 128L448 112C448 103.2 455.2 96 464 96zM544 256L544 272C544 316.2 508.2 352 464 352C455.2 352 448 344.8 448 336L448 320C448 275.8 483.8 240 528 240C536.8 240 544 247.2 544 256zM416 240C424.8 240 432 247.2 432 256L432 272C432 316.2 396.2 352 352 352C343.2 352 336 344.8 336 336L336 320C336 275.8 371.8 240 416 240zM320 256L320 272C320 316.2 284.2 352 240 352C231.2 352 224 344.8 224 336L224 320C224 275.8 259.8 240 304 240C312.8 240 320 247.2 320 256zM67.5 411.6C65.6 396.9 77 384 91.8 384L548.2 384C563 384 574.4 396.9 572.6 411.6C566.3 461.8 528.2 501 480 510L480 512C480 529.7 465.7 544 448 544L192 544C174.3 544 160 529.7 160 512L160 510C111.8 501 73.7 461.8 67.5 411.6z"/>
                    </svg>
                    <?= trans("ingredients"); ?>
                </h3>
                <div class="card-toolbar rotate-180">
                    <i class="ki-duotone ki-down fs-1"></i>
                </div>
            </div>
            <div id="ingredients_card_collapsible" class="collapse <?= empty($post) ? 'show' : ''; ?>">
                <div class="card-body pt-5">
                    <div id="ingredients-container" class="d-flex flex-column gap-3 mb-3">
                        <?php if (!empty($dataRecipe) && !empty($dataRecipe->ingredients)):
                            foreach ($dataRecipe->ingredients as $itemIngredient): ?>
                                <div class="fv-row d-flex gap-3 align-items-center recipe-ingredient">
                                    <input type="text" name="recipe_ingredient[]" value="<?= esc($itemIngredient); ?>" class="form-control" placeholder="<?= trans('ingredient'); ?>">
                                    <button type="button" class="btn btn-sm btn-icon flex-shrink-0 btn-light-danger btn-remove-ingredient">
                                        <i class="ki-duotone ki-trash fs-5">
                                            <span class="path1"></span><span class="path2"></span>
                                            <span class="path3"></span><span class="path4"></span><span class="path5"></span>
                                        </i>
                                    </button>
                                </div>
                            <?php endforeach;
                        endif; ?>
                    </div>

                    <div class="mb-6 fv-row">
                        <button type="button" id="btn-add-ingredient" class="btn btn-sm btn-light-success">
                            <i class="ki-duotone ki-plus fs-4"></i>
                            <?= trans("add_new"); ?>
                        </button>
                    </div>

                    <div class="text-gray-500 mt-1 fw-semibold fs-7"><?= trans("ingredient_ex"); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card card-flush">
            <div class="card-header collapsible cursor-pointer rotate <?= !empty($post) ? 'collapsed' : 'active'; ?>" data-bs-toggle="collapse" data-bs-target="#nutritional_card_collapsible">
                <h3 class="card-title fw-bold fs-3 gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="22" height="22" fill="#78829D">
                        <path d="M496 640C416.5 640 352 575.5 352 496C352 416.5 416.5 352 496 352C575.5 352 640 416.5 640 496C640 575.5 575.5 640 496 640zM81 593C71.6 602.3 56.5 602.3 47.1 593C37.7 583.6 37.7 568.4 47.1 559.1L81 593zM111.2 298.6C117.4 294.5 125.9 295.2 131.3 300.7L177.4 346.8L183.5 353.5C203.2 377.3 209.8 408.5 202.8 437.4C234.5 429.7 269 438.4 293.4 462.7L305.4 474.7C304.6 481.7 304.2 488.9 304.2 496.1C304.2 516.1 307.2 535.4 312.9 553.5C276.1 575.3 227.9 570.5 196.3 538.8L165.6 508.3L81 593C69.7 581.7 58.4 570.3 47 559L131.7 474.3L101.2 443.8C63.7 406.3 63.7 345.5 101.2 308.1L108.6 300.7L111.1 298.6zM496 540C485 540 476 549 476 560C476 571 485 580 496 580C507 580 516 571 516 560C516 549 507 540 496 540zM496 400C487.2 400 480 407.2 480 416L480 496C480 504.8 487.2 512 496 512C504.8 512 512 504.8 512 496L512 416C512 407.2 504.8 400 496 400zM215.2 194.6C221.4 190.5 229.9 191.2 235.3 196.7L281.4 242.8L287.5 249.5C307.2 273.3 313.8 304.5 306.8 333.4C331 327.5 356.9 331.3 378.7 344.2C346.5 369.1 322.5 404 311.3 444.3C307.5 441.5 303.8 438.3 300.3 434.8L205.4 339.9C167.9 302.4 167.9 241.6 205.4 204.2L212.8 196.8L215.3 194.7zM526.7 79C536.1 69.6 551.3 69.6 560.6 79C569.4 87.8 569.9 101.7 562.3 111.1L560.6 113L446.2 227.4C453.9 228.4 461.5 230.4 468.7 233.3L527 175C536.4 165.6 551.6 165.6 561 175C569.8 183.8 570.3 197.7 562.6 207.1L560.9 208.9L508.2 261.6L547.2 300.6C550.4 303.8 551.9 308 551.9 312.2C534.2 306.8 515.4 303.9 496 303.9C461.8 303.9 429.7 312.8 401.9 328.5L309.2 235.8C271.7 198.3 271.7 137.5 309.2 100L316.6 92.6L319.1 90.5C325.3 86.4 333.8 87.1 339.2 92.6L378.2 131.6L430.9 78.9C440.3 69.5 455.5 69.5 464.9 78.9C473.7 87.7 474.2 101.6 466.5 111L465 113L406.7 171.3C409.5 178.4 411.4 185.8 412.5 193.4L526.7 79z"/>
                    </svg>
                    <?= trans("nutritional_information"); ?>
                </h3>
                <div class="card-toolbar rotate-180">
                    <i class="ki-duotone ki-down fs-1"></i>
                </div>
            </div>
            <div id="nutritional_card_collapsible" class="collapse <?= empty($post) ? 'show' : ''; ?>">
                <div class="card-body pt-5">
                    <div id="nutritionals-container" class="d-flex flex-column gap-3 mb-3">
                        <?php if (!empty($dataRecipe) && !empty($dataRecipe->nInfo)):
                            foreach ($dataRecipe->nInfo as $itemInfo):
                                $nutId = uniqid();
                                $nutName = !empty($itemInfo) && !empty($itemInfo->n) ? $itemInfo->n : '';
                                $nutVal = !empty($itemInfo) && !empty($itemInfo->v) ? $itemInfo->v : ''; ?>
                                <div class="fv-row d-flex gap-3 align-items-center recipe-nutritional">
                                    <input type="text" name="recipe_nutritional[<?= $nutId; ?>][name]" value="<?= esc($nutName); ?>" class="form-control" placeholder="<?= trans("name"); ?>">
                                    <input type="text" name="recipe_nutritional[<?= $nutId; ?>][value]" value="<?= esc($nutVal); ?>" class="form-control" placeholder="<?= trans("value"); ?>">
                                    <button type="button" class="btn btn-sm btn-icon flex-shrink-0 btn-light-danger btn-remove-nutritional">
                                        <i class="ki-duotone ki-trash fs-5">
                                            <span class="path1"></span><span class="path2"></span>
                                            <span class="path3"></span><span class="path4"></span><span class="path5"></span>
                                        </i>
                                    </button>
                                </div>
                            <?php endforeach;
                        endif; ?>
                    </div>

                    <div class="mb-6 fv-row">
                        <button type="button" id="btn-add-nutritional" class="btn btn-sm btn-light-success">
                            <i class="ki-duotone ki-plus fs-4"></i>
                            <?= trans("add_new"); ?>
                        </button>
                    </div>

                    <div class="text-gray-500 mt-1 fw-semibold fs-7"><?= trans("nutritional_ex"); ?></div>
                </div>
            </div>
        </div>
    </div>

</div>

<?= $this->section('scripts'); ?>
<script>
    $(document).ready(function () {
        var $ingredientsContainer = $('#ingredients-container');
        var $nutritionalsContainer = $('#nutritionals-container');

        $('#btn-add-ingredient').on('click', function () {
            var newOptionHTML = `
            <div class="fv-row d-flex gap-3 align-items-center recipe-ingredient">
                <input type="text" name="recipe_ingredient[]" class="form-control" placeholder="<?= trans('ingredient', 'js'); ?>">
                <button type="button" class="btn btn-sm btn-icon flex-shrink-0 btn-light-danger btn-remove-ingredient">
                    <i class="ki-duotone ki-trash fs-5">
                        <span class="path1"></span><span class="path2"></span>
                        <span class="path3"></span><span class="path4"></span><span class="path5"></span>
                    </i>
                </button>
            </div>`;
            $ingredientsContainer.append(newOptionHTML);
        });

        $('#btn-add-nutritional').on('click', function () {
            const nutId = Date.now() + '-' + Math.floor(Math.random() * 1000);
            var newOptionHTML = `
            <div class="fv-row d-flex gap-3 align-items-center recipe-nutritional">
                <input type="text" name="recipe_nutritional[${nutId}][name]" class="form-control" placeholder="<?= trans("name", "js"); ?>">
                <input type="text" name="recipe_nutritional[${nutId}][value]" class="form-control" placeholder="<?= trans("value", "js"); ?>">
                <button type="button" class="btn btn-sm btn-icon flex-shrink-0 btn-light-danger btn-remove-nutritional">
                    <i class="ki-duotone ki-trash fs-5">
                        <span class="path1"></span><span class="path2"></span>
                        <span class="path3"></span><span class="path4"></span><span class="path5"></span>
                    </i>
                </button>
            </div>`;
            $nutritionalsContainer.append(newOptionHTML);
        });

        $(document).on('click', '.btn-remove-ingredient', function () {
            Swal.fire(swalOptions(VrConfig.text.confirmDelete)).then((result) => {
                if (result.isConfirmed) {
                    $(this).closest('.recipe-ingredient').remove();
                }
            });
        });

        $(document).on('click', '.btn-remove-nutritional', function () {
            Swal.fire(swalOptions(VrConfig.text.confirmDelete)).then((result) => {
                if (result.isConfirmed) {
                    $(this).closest('.recipe-nutritional').remove();
                }
            });
        });
    });
</script>
<?= $this->endSection(); ?>