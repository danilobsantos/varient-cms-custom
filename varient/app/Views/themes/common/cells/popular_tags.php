<?php if (!empty($tags)): ?>
    <ul class="tag-list">
        <?php foreach ($tags as $item): ?>
            <li><a href="<?= generateTagURL($item->tag_slug); ?>"><?= esc($item->tag); ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>