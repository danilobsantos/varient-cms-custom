<div class="profile-social-links">
    <?php $socialLinks = getUserSocialLinks($user, $baseSettings);

    if (!empty($socialLinks)): ?>
        <?php foreach ($socialLinks as $link): ?>
            <a href="<?= esc($link->url); ?>" target="_blank" rel="nofollow noopener" class="link-social js-hover-bg"
               title="<?= esc($link->name); ?>" aria-label="<?= esc($link->name); ?>" data-bg="<?= esc($link->color, "attr"); ?>" data-color="#ffffff">
                <?= $link->svg; ?>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ((int)$config->show_rss === 1 && (int)$user->show_rss_feeds === 1):
        $rssIcon = getAppDefault('rssIcon'); ?>
        <a href="<?= generateURL('rss_feeds') . '/feed/author/' . $user->slug; ?>" rel="noopener" class="link-social js-hover-bg"
           target="_blank" aria-label="RSS Feed" title="RSS Feed" data-bg="<?= esc($rssIcon['color'] ?? '', 'attr'); ?>" data-color="#ffffff">
            <?= $rssIcon['svg'] ?? ''; ?>
        </a>
    <?php endif; ?>
</div>