<nav aria-label="breadcrumb">
    <ol class="breadcrumb">

        <li class="breadcrumb-item">
            <a href="<?= langBaseUrl(); ?>"><?= trans("home") ?></a>
        </li>

        <?php if (!empty($breadcrumb)): ?>
            <?php foreach ($breadcrumb as $item): ?>
                <li class="breadcrumb-item">
                    <a href="<?= generateCategoryUrl($item) ?>">
                        <?= esc($item->name) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>

        <li class="breadcrumb-item active" aria-current="page">
            <?= esc(getDisplayTitle($post->title, 160)); ?>
        </li>

    </ol>
</nav>