<?php
$listTitle = match ($postFormat) {
    'gallery' => trans("gallery_items"),
    'table_of_contents' => trans("list_items"),
    'recipe' => trans("directions"),
    default => trans("list_items")
};
?>

<div class="mt-3">
    <h2 class="mb-4 fw-bolder"><?= esc($listTitle); ?></h2>

    <div id="listItemsContainer">
        <?php if (!empty($postItems)) {
            foreach ($postItems as $postItem) {
                echo view('admin/post/_list_item', ['postFormat' => $postFormat, 'postItem' => $postItem]);
            }
        } else {
            echo view('admin/post/_list_item', ['postFormat' => $postFormat, 'numItems' => 0]);
        } ?>
    </div>

    <div class="d-flex justify-content-center mt-6 mb-10">
        <button type="button" class="btn btn-success btn-add-list-item" data-post-format="<?= esc($postFormat); ?>">
            <i class="ki-duotone ki-plus fs-2"></i>
            <?= trans("add_new_item"); ?>
        </button>
    </div>
</div>