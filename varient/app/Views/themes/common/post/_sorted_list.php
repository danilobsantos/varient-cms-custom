<?php
$paywallType = !empty($premiumMembership->paywallAppearance) ? $premiumMembership->paywallAppearance : 'fade';
$itemCount = 1;

if (!empty($postListItems)):
    foreach ($postListItems as $listItem):
        if (!$hasAccess && $itemCount > 1) {
            break;
        }
        ?>
        <div class="ordered-list-item">
            <h3 class="title-post-item">
                <?php if ($post->show_item_numbers) {
                    echo $itemCount . '. ' . esc($listItem->title);
                } else {
                    echo esc($listItem->title);
                } ?>
            </h3>

            <?php if (!empty($listItem->image_default)):
                $imgUrl = getStorageFileUrl($listItem->image_default, $listItem->storage); ?>
                <div class="post-image">
                    <div class="post-image-inner">
                        <img src="<?= esc($imgUrl); ?>" alt="<?= esc($listItem->title); ?>" class="img-fluid" width="856" height="570"/>
                        <figcaption class="img-description"><?= esc($listItem->image_description); ?></figcaption>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($hasAccess): ?>
                <div class="entry-content">
                    <?= $listItem->content; ?>
                </div>
            <?php else:
                if ($paywallType === 'hard'):
                    echo loadCommonView('premium/_paywall', [
                            'restrictionType' => $restrictionType,
                            'paywallType'     => 'hard'
                    ]);
                else: ?>
                    <div style="position: relative; overflow: hidden; min-height: 520px;">
                        <div class="entry-content">
                            <?php
                            $arrayContent = !empty($listItem->content) ? explode('</p>', $listItem->content) : [];
                            if (!empty($arrayContent)) {
                                foreach ($arrayContent as $p) {
                                    if (trim($p) !== '') {
                                        echo $p . '</p>';
                                        break;
                                    }
                                }
                            }
                            ?>
                        </div>
                        <?php echo loadCommonView('premium/_paywall', [
                                'restrictionType' => $restrictionType,
                                'paywallType'     => 'fade'
                        ]); ?>
                    </div>
                <?php endif;
            endif; ?>
        </div>
        <?php
        if (!$hasAccess) {
            break;
        }
        $itemCount++;
    endforeach;
endif;
?>