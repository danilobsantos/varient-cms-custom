<div class="card card-flush h-100">
    <div class="card-header pt-7">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold text-gray-900"><?= trans("pending_posts"); ?></span>
            <span class="text-gray-500 mt-1 fw-semibold fs-6"><?= trans("pending_posts_exp"); ?></span>
        </h3>

        <div class="card-toolbar">
            <div class="d-flex flex-stack flex-wrap gap-4">
                <a href="<?= adminUrl("pending-posts"); ?>" class="btn btn-light btn-sm"><?= trans("view_all"); ?></a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="hover-scroll-overlay-y" style="max-height: 420px;">
            <table class="table align-middle table-row-dashed fs-6 gy-3">
                <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-20px"><?= trans('id'); ?></th>
                    <th class="min-w-125px"><?= trans('post'); ?></th>
                    <th class="min-w-125px"><?= trans('author'); ?></th>
                    <th class="min-w-125px"><?= trans('post_format'); ?></th>
                    <th><?= trans('date'); ?></th>
                </tr>
                </thead>
                <tbody class="fw-bold text-gray-600">
                <?php if (!empty($latestPendingPosts)):
                    foreach ($latestPendingPosts as $item):
                        $language = getLanguageClient($item->lang_id, $config);
                        $postUrl = generateBaseURLByLang($language) . 'post/preview/' . $item->slug; ?>
                        <tr>
                            <td><?= esc($item->id); ?></td>
                            <td class="d-flex mw-400px">
                                <div class="symbol symbol-60px overflow-hidden me-3">
                                    <a href="<?= esc($postUrl); ?>" target="_blank">
                                        <div class="symbol-label">
                                            <img src="<?= getPostImageUrl($item, "small"); ?>" alt=""
                                                 class="w-60px h-60px object-fit-cover" loading="lazy"/>
                                        </div>
                                    </a>
                                </div>
                                <div class="d-flex flex-column">
                                    <a href="<?= esc($postUrl); ?>" class="text-gray-800 text-hover-primary mb-1"
                                       target="_blank">
                                        <?= esc(getDisplayTitle($item->title)); ?>
                                    </a>
                                </div>
                            </td>
                            <td><?= trans($item->post_format); ?></td>
                            <td>
                                <a href="<?= generateProfileURL($item->author_slug); ?>" target="_blank"
                                   class="text-gray-800 text-hover-primary">
                                    <?= esc($item->author_username); ?>
                                </a>
                            </td>
                            <td><?= $item->created_at; ?></td>
                        </tr>
                    <?php endforeach;
                endif; ?>
                </tbody>
            </table>

            <?php if (empty($latestPendingPosts)): ?>
                <div class="d-flex flex-column align-items-center justify-content-center h-100 py-10">
                    <span class="text-muted fw-semibold fs-6">
                        <?= trans("no_records_found"); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>