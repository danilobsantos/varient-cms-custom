<?php $socialLinks = getSiteSocialLinks($baseSettings);
if (!empty($socialLinks)):
    foreach ($socialLinks as $link):
        if (!empty($link->url)):
            $socialKey = $link->key ?? '';
            $socialName = trans($socialKey) ?? ''; ?>
            <a href="<?= esc($link->url); ?>" target="_blank" rel="nofollow noopener" class="js-hover-bg"
               title="<?= esc($socialName, "attr"); ?>" aria-label="<?= esc($socialName, "attr"); ?>" data-bg="<?= esc($link->color, 'attr'); ?>" data-color="#ffffff">
                <?= $link->svg; ?>
            </a>
        <?php endif;
    endforeach;
endif;

if (!empty($config->show_rss) && $rssHide == false) :
    $rssIcon = getAppDefault('rssIcon'); ?>
    <a href="<?= generateURL('rss_feeds'); ?>" class="js-hover-bg" aria-label="RSS Feed" title="RSS Feed" data-bg="<?= esc($rssIcon['color'] ?? '', 'attr'); ?>" data-color="#ffffff">
        <?= $rssIcon['svg'] ?? ''; ?>
    </a>
<?php endif; ?>