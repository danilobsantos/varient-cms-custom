<div class="d-flex flex-column gap-5 gap-xl-7 gap-xxl-10">

    <?php if (in_array($postFormat, ['video'])): ?>
        <?= view("admin/post/sidebar/_video"); ?>
    <?php endif; ?>

    <?php if (in_array($postFormat, ['audio'])): ?>
        <?= view("admin/post/sidebar/_audio"); ?>
    <?php endif; ?>

    <?= view("admin/post/sidebar/_image"); ?>

    <?php if (in_array($postFormat, ['recipe'])): ?>
        <?= view("admin/post/sidebar/_video", ['videoType' => 'recipe']); ?>
    <?php endif; ?>

    <?php if (in_array($postFormat, ['event'])): ?>
        <?= view("admin/post/event/_event_details"); ?>
    <?php endif; ?>

    <?= view("admin/post/sidebar/_settings"); ?>

    <?= view("admin/post/sidebar/_premium", ['premiumObject' => $post ?? null, 'premiumObjectType' => 'post']); ?>

</div>