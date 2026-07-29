<?php if (!empty($galleryPostItem)):
    if ($galleryPostNumRows > 0):
        $paywallType = !empty($premiumMembership->paywallAppearance) ? $premiumMembership->paywallAppearance : 'fade';
        $isHardPaywall = (!$hasAccess && ($paywallType === 'hard' || $pageNumber > 1));
        ?>
        <div class="gallery-post-item">

            <?php if (!empty($galleryPostItem->image_default)):
                $imgUrl = getStorageFileUrl($galleryPostItem->image_default, $galleryPostItem->storage); ?>
                <div class="post-image mb-4">
                    <div class="post-image-inner">
                        <img src="<?= esc($imgUrl); ?>" alt="<?= esc($galleryPostItem->title); ?>" class="img-fluid" width="856" height="570" loading="lazy"/>
                    </div>
                    <figcaption class="img-description"><?= esc($galleryPostItem->image_description); ?></figcaption>
                    <div class="post-item-count">
                        <?= esc($pageNumber); ?>&nbsp;/&nbsp;<?= esc($galleryPostNumRows); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!$isHardPaywall): ?>
                <h2 class="title-post-item">
                    <?php if ($post->show_item_numbers):
                        echo esc($pageNumber) . ". " . esc($galleryPostItem->title);
                    else:
                        echo esc($galleryPostItem->title);
                    endif; ?>
                </h2>
            <?php endif; ?>

            <div class="entry-content">
                <?php
                if ($hasAccess) {
                    echo $galleryPostItem->content;
                } else {
                    if ($isHardPaywall) {
                        echo loadCommonView('premium/_paywall', [
                                'restrictionType' => $restrictionType,
                                'paywallType'     => 'hard'
                        ]);
                    } else {
                        $arrayContent = !empty($galleryPostItem->content) ? explode('</p>', $galleryPostItem->content) : [];

                        echo '<div style="position: relative; overflow: hidden; min-height: 480px;">';

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

                        echo '</div>';
                    }
                }
                ?>
            </div>

            <?php if ($hasAccess): ?>
                <div class="d-flex justify-content-between mb-5 mt-5">
                    <?php $postUrl = generatePostUrl($post);
                    if (!isPostPublished($post)) {
                        $postUrl = langBaseUrl('preview/' . $post->slug);
                    } ?>
                    <div class="item">
                        <?php if ($pageNumber != 1): ?>
                            <a href="<?= $postUrl; ?>?p=<?= $pageNumber - 1; ?>" class="btn btn-custom gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 640 640" fill="currentColor">
                                    <path d="M73.4 297.4C60.9 309.9 60.9 330.2 73.4 342.7L233.4 502.7C245.9 515.2 266.2 515.2 278.7 502.7C291.2 490.2 291.2 469.9 278.7 457.4L173.3 352L544 352C561.7 352 576 337.7 576 320C576 302.3 561.7 288 544 288L173.3 288L278.7 182.6C291.2 170.1 291.2 149.8 278.7 137.3C266.2 124.8 245.9 124.8 233.4 137.3L73.4 297.3z"/>
                                </svg>
                                <?= trans("previous"); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="item">
                        <?php if ($pageNumber < $galleryPostNumRows): ?>
                            <a href="<?= $postUrl; ?>?p=<?= $pageNumber + 1; ?>" class="btn btn-custom gap-2">
                                <?= trans("next"); ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 640 640" fill="currentColor">
                                    <path d="M566.6 342.6C579.1 330.1 579.1 309.8 566.6 297.3L406.6 137.3C394.1 124.8 373.8 124.8 361.3 137.3C348.8 149.8 348.8 170.1 361.3 182.6L466.7 288L96 288C78.3 288 64 302.3 64 320C64 337.7 78.3 352 96 352L466.7 352L361.3 457.4C348.8 469.9 348.8 490.2 361.3 502.7C373.8 515.2 394.1 515.2 406.6 502.7L566.6 342.7z"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif;
endif; ?>