<?php
$linkNums = [];
$linkTree = [];
$processedIds = [];

if (!empty($postListItems)) {
    // Collect all item orders first for parent validation
    foreach ($postListItems as $item) {
        $linkNums[] = $item->item_order;
    }

    $i = 0;
    foreach ($postListItems as $item) {
        // Prevent duplicates
        if (!in_array($item->id, $processedIds)) {
            $processedIds[] = $item->id;

            // Determine if it's a Root item or a Child item
            // Logic: If parent_link_num exists and is valid, it's a child.
            if ($i > 0 && !empty($item->parent_link_num) && in_array($item->parent_link_num, $linkNums)) {
                $linkTree[$item->parent_link_num][] = $item;
            } else {
                $linkTree[0][] = $item; // Root items
            }
            $i++;
        }
    }
}

$paywallType = !empty($premiumMembership->paywallAppearance) ? $premiumMembership->paywallAppearance : 'fade';
$isHardPaywall = (!$hasAccess && $paywallType === 'hard');
?>

<?= loadCommonView('post/_post_image_preview', ['post' => $post, 'postImages' => $postImages]); ?>

<?php if (!$isHardPaywall): ?>
    <div class="row">
        <div class="col-12 m-b-15">
            <div class="table-of-contents">
                <div class="title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20" fill="currentColor">
                        <path d="M64 144a48 48 0 1 0 0-96 48 48 0 1 0 0 96zM192 64c-17.7 0-32 14.3-32 32s14.3 32 32 32H480c17.7 0 32-14.3 32-32s-14.3-32-32-32H192zm0 160c-17.7 0-32 14.3-32 32s14.3 32 32 32H480c17.7 0 32-14.3 32-32s-14.3-32-32-32H192zm0 160c-17.7 0-32 14.3-32 32s14.3 32 32 32H480c17.7 0 32-14.3 32-32s-14.3-32-32-32H192zM64 464a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm48-208a48 48 0 1 0 -96 0 48 48 0 1 0 96 0z"/>
                    </svg>&nbsp;&nbsp;
                    <h2> <?= trans("table_of_contents"); ?></h2>
                </div>

                <?php
                if (!empty($linkTree[0])): ?>
                    <ul class="ul-main">
                        <?php
                        $displayedIds = [];
                        foreach ($linkTree[0] as $item0):
                            if (in_array($item0->id, $displayedIds)) continue;
                            $displayedIds[] = $item0->id; ?>
                            <li style="list-style: <?= getPostListStyle($post, 1)->style; ?>">
                                <a href="#<?= strSlug($item0->title) . '-' . $item0->id; ?>" data-id="toc-<?= $item0->id; ?>" class="link-table-of-content"><?= esc($item0->title); ?></a>

                                <?php if (!empty($linkTree[$item0->item_order])): ?>
                                    <ul>
                                        <?php foreach ($linkTree[$item0->item_order] as $item1):
                                            if (in_array($item1->id, $displayedIds)) continue;
                                            $displayedIds[] = $item1->id;
                                            ?>
                                            <li style="list-style: <?= getPostListStyle($post, 2)->style; ?>">
                                                <a href="#<?= strSlug($item1->title) . '-' . $item1->id; ?>" data-id="toc-<?= $item1->id; ?>" class="link-table-of-content"><?= esc($item1->title); ?></a>

                                                <?php if (!empty($linkTree[$item1->item_order])): ?>
                                                    <ul>
                                                        <?php foreach ($linkTree[$item1->item_order] as $item2):
                                                            if (in_array($item2->id, $displayedIds)) continue;
                                                            $displayedIds[] = $item2->id;
                                                            ?>
                                                            <li style="list-style: <?= getPostListStyle($post, 3)->style; ?>">
                                                                <a href="#<?= strSlug($item2->title) . '-' . $item2->id; ?>" data-id="toc-<?= $item2->id; ?>" class="link-table-of-content"><?= esc($item2->title); ?></a>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="container-toc">
                <?php if (!empty($linkTree[0])):
                    if (!$hasAccess):
                        if ($isHardPaywall):
                            echo loadCommonView('premium/_paywall', [
                                    'restrictionType' => $restrictionType,
                                    'paywallType'     => 'hard'
                            ]);
                        else:
                            $item0 = $linkTree[0][0];
                            $slug0 = strSlug($item0->title) . '-' . $item0->id;
                            $style0 = getPostListStyle($post, 1)->status == 1 ? getPostListStyle($post, 1)->style : 'none';
                            ?>
                            <ul class="ul-toc-content ul-toc-content-main">
                                <li class="li-toc-content li-toc-content-main">
                                    <div data-title="<?= $slug0; ?>">
                                        <h3 class="title-post-item" style="list-style: <?= $style0; ?>;list-style-position: inside;"><?= esc($item0->title); ?></h3>
                                        <?php if (!empty($item0->image_default)): ?>
                                            <div class="post-image">
                                                <div class="post-image-inner">
                                                    <img src="<?= esc(getStorageFileUrl($item0->image_default, $item0->storage)); ?>" alt="<?= esc($item0->title); ?>" class="img-fluid" width="856" height="570"/>
                                                    <figcaption class="img-description"><?= esc($item0->image_description); ?></figcaption>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="entry-content">
                                            <div style="position: relative; overflow: hidden; min-height: 520px;">
                                                <?php
                                                $arrayContent = !empty($item0->content) ? explode('</p>', $item0->content) : [];
                                                if (!empty($arrayContent)) {
                                                    foreach ($arrayContent as $p) {
                                                        if (trim($p) !== '') {
                                                            echo $p . '</p>';
                                                            break;
                                                        }
                                                    }
                                                }
                                                echo loadCommonView('premium/_paywall', [
                                                        'restrictionType' => $restrictionType,
                                                        'paywallType'     => 'fade'
                                                ]);
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        <?php endif; ?>
                    <?php else: ?>
                        <ul class="ul-toc-content ul-toc-content-main">
                            <?php
                            $contentDisplayedIds = [];
                            foreach ($linkTree[0] as $item0):
                                if (in_array($item0->id, $contentDisplayedIds)) continue;
                                $contentDisplayedIds[] = $item0->id;
                                $slug0 = strSlug($item0->title) . '-' . $item0->id;
                                $style0 = getPostListStyle($post, 1)->status == 1 ? getPostListStyle($post, 1)->style : 'none'; ?>
                                <li class="li-toc-content li-toc-content-main">
                                    <div data-title="<?= $slug0; ?>">
                                        <h3 class="title-post-item" style="list-style: <?= $style0; ?>;list-style-position: inside;"><?= esc($item0->title); ?></h3>
                                        <?php if (!empty($item0->image_default)): ?>
                                            <div class="post-image">
                                                <div class="post-image-inner">
                                                    <img src="<?= esc(getStorageFileUrl($item0->image_default, $item0->storage)); ?>" alt="<?= esc($item0->title); ?>" class="img-fluid" width="856" height="570"/>
                                                    <figcaption class="img-description"><?= esc($item0->image_description); ?></figcaption>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="entry-content"><?= $item0->content; ?></div>
                                    </div>

                                    <?php if (!empty($linkTree[$item0->item_order])): ?>
                                        <ul class="ul-toc-content ul-toc-content-sub">
                                            <?php foreach ($linkTree[$item0->item_order] as $item1):
                                                if (in_array($item1->id, $contentDisplayedIds)) continue;
                                                $contentDisplayedIds[] = $item1->id;
                                                $slug1 = strSlug($item1->title) . '-' . $item1->id;
                                                $style1 = getPostListStyle($post, 2)->status == 1 ? getPostListStyle($post, 2)->style : 'none'; ?>
                                                <li class="li-toc-content">
                                                    <div data-title="<?= $slug1; ?>">
                                                        <h3 class="title-post-item" style="list-style: <?= $style1; ?>;list-style-position: inside;"><?= esc($item1->title); ?></h3>
                                                        <?php if (!empty($item1->image_default)): ?>
                                                            <div class="post-image">
                                                                <div class="post-image-inner">
                                                                    <img src="<?= esc(getStorageFileUrl($item1->image_default, $item1->storage)); ?>" alt="<?= esc($item1->title); ?>" class="img-fluid" width="856" height="570"/>
                                                                    <figcaption class="img-description"><?= esc($item1->image_description); ?></figcaption>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="entry-content"><?= $item1->content; ?></div>
                                                    </div>

                                                    <?php if (!empty($linkTree[$item1->item_order])): ?>
                                                        <ul class="ul-toc-content ul-toc-content-sub ul-toc-content-sub-sub">
                                                            <?php foreach ($linkTree[$item1->item_order] as $item2):
                                                                if (in_array($item2->id, $contentDisplayedIds)) continue;
                                                                $contentDisplayedIds[] = $item2->id;
                                                                $slug2 = strSlug($item2->title) . '-' . $item2->id;
                                                                $style2 = getPostListStyle($post, 3)->status == 1 ? getPostListStyle($post, 3)->style : 'none'; ?>
                                                                <li class="li-toc-content">
                                                                    <div data-title="<?= $slug2; ?>">
                                                                        <h3 class="title-post-item" style="list-style: <?= $style2; ?>;list-style-position: inside;"><?= esc($item2->title); ?></h3>
                                                                        <?php if (!empty($item2->image_default)): ?>
                                                                            <div class="post-image">
                                                                                <div class="post-image-inner">
                                                                                    <img src="<?= esc(getStorageFileUrl($item2->image_default, $item2->storage)); ?>" alt="<?= esc($item2->title); ?>" class="img-fluid" width="856" height="570"/>
                                                                                    <figcaption class="img-description"><?= esc($item2->image_description); ?></figcaption>
                                                                                </div>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                        <div class="entry-content"><?= $item2->content; ?></div>
                                                                    </div>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?= $this->section('scripts'); ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', () => {
            // Handle hash on page load
            if (window.location.hash) {
                const hash = decodeURIComponent(window.location.hash.substring(1));
                scrollToSection(hash, false);
            }

            const scrollLinks = document.querySelectorAll('.link-table-of-content');

            scrollLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    // Prevent default anchor jump behavior
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    const hash = link.getAttribute('href');

                    // Update URL hash without jumping
                    history.pushState(null, null, hash);

                    // Scroll to the specific section
                    scrollToSection(hash.substring(1), true);
                });
            });
        });

        function scrollToSection(title, isUserClick = false) {
            const element = document.querySelector(`[data-title="${title}"]`);

            if (element) {
                element.scrollIntoView({behavior: 'smooth'});
            } else if (isUserClick) {
                // Handle paywall or restricted access
                if (typeof verifyContentAccess === 'function') {
                    verifyContentAccess();
                } else {
                    const modalId = '<?= (isset($post->auth_type) && $post->auth_type === 'exclusive') ? 'exclusiveActionModal' : 'premiumActionModal' ?>';
                    const modalEl = document.getElementById(modalId);
                    if (modalEl) {
                        const actionModal = new bootstrap.Modal(modalEl);
                        actionModal.show();
                    }
                }
            }
        }
    </script>
<?= $this->endSection(); ?>