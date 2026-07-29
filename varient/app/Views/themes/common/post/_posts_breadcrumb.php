<?php
$breadcrumb = match ($pageType ?? null) {
    'posts' => [
            [
                    'title' => trans('posts'),
                    'url'   => null,
            ],
    ],
    'category' => [
            [
                    'title' => trans('posts'),
                    'url'   => generateURL('posts'),
            ],
        // Insert parent category if it exists
            ...(!empty($parentCategory) ? [[
                    'title' => $parentCategory->name ?? '',
                    'url'   => generateCategoryUrl($parentCategory),
            ]] : []),
            [
                    'title' => $category->name ?? '',
                    'url'   => null,
            ],
    ],
    'search' => [
            [
                    'title' => trans('posts'),
                    'url'   => generateURL('posts'),
            ],
            [
                    'title' => ($q ?? '') !== '' ? trans('search') . ': ' . $q : trans('search'),
                    'url'   => null,
            ],
    ],
    'tag' => [
            [
                    'title' => trans('posts'),
                    'url'   => generateURL('posts'),
            ],
            [
                    'title' => $tag->tag ?? '',
                    'url'   => null,
            ],
    ],
    'reading_list' => [
            [
                    'title' => trans('reading_list'),
                    'url'   => null,
            ],
    ],
    default => [],
}; ?>

<?php if (!empty($breadcrumb)): ?>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= langBaseUrl(); ?>"><?= trans('home', 'attr'); ?></a></li>
            <?php $lastIndex = array_key_last($breadcrumb);
            foreach ($breadcrumb as $index => $item):
                $isLast = ($index === $lastIndex); ?>
                <?php if ($isLast): ?>
                <li class="breadcrumb-item active" aria-current="page"><span><?= esc($item['title']); ?></span></li>
            <?php else:
                if (!empty($item['url'])): ?>
                    <li class="breadcrumb-item"><a href="<?= esc($item['url']); ?>"><?= esc($item['title']); ?></a>
                    </li>
                <?php endif;
            endif;
            endforeach; ?>
        </ol>
    </nav>
<?php endif; ?>