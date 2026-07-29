<?php
$postURL = urlencode(generatePostUrl($post));
$postTitle = urlencode($post->title);
$postImage = getPostImageUrl($post, 'default');
$socialPlatforms = getSocialPlatforms();
?>
<div class="d-flex flex-wrap w-100 justify-content-between align-items-center gap-2 py-2 share-flex-wrapper">
    <div class="d-flex align-items-center left gap-2" role="group" aria-label="<?= trans("share_post", "attr"); ?>">
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $postURL; ?>"
           onclick="window.open(this.href, 'Share', 'width=640,height=450'); return false;"
           class="btn-social-share" title="Facebook" aria-label="Share on Facebook" rel="noopener noreferrer" style="background-color: <?= $socialPlatforms['facebook']['color'] ?? '#1877F2'; ?>;">
            <?= $socialPlatforms['facebook']['svg'] ?? ''; ?>
        </a>

        <a href="https://twitter.com/share?url=<?= $postURL; ?>&amp;text=<?= $postTitle; ?>"
           onclick="window.open(this.href, 'Share', 'width=640,height=450'); return false;"
           class="btn-social-share" title="X (Twitter)" aria-label="Share on X" rel="noopener noreferrer" style="background-color: <?= $socialPlatforms['x_twitter']['color'] ?? '#000000'; ?>;">
            <?= $socialPlatforms['x_twitter']['svg'] ?? ''; ?>
        </a>

        <a href="https://api.whatsapp.com/send?text=<?= $postTitle; ?> - <?= $postURL; ?>"
           target="_blank" class="btn-social-share" title="WhatsApp" aria-label="Share on WhatsApp" rel="noopener noreferrer" style="background-color: <?= $socialPlatforms['whatsapp']['color'] ?? '#25D366'; ?>;">
            <?= $socialPlatforms['whatsapp']['svg'] ?? ''; ?>
        </a>

        <a href="http://www.linkedin.com/shareArticle?mini=true&amp;url=<?= $postURL; ?>"
           onclick="window.open(this.href, 'Share', 'width=640,height=450'); return false;"
           class="btn-social-share" title="LinkedIn" aria-label="Share on LinkedIn" rel="noopener noreferrer" style="background-color: <?= $socialPlatforms['linkedin']['color'] ?? '#0077B5'; ?>;">
            <?= $socialPlatforms['linkedin']['svg'] ?? ''; ?>
        </a>

        <a href="https://t.me/share/url?url=<?= $postURL; ?>&text=<?= $postTitle; ?>"
           target="_blank" class="btn-social-share" title="Telegram" aria-label="Share on Telegram" rel="noopener noreferrer" style="background-color: <?= $socialPlatforms['telegram']['color'] ?? '#26A5E4'; ?>;">
            <?= $socialPlatforms['telegram']['svg'] ?? ''; ?>
        </a>

        <a href="mailto:?subject=<?= $postTitle; ?>&body=<?= $postURL; ?>"
           class="btn-social-share color-bg-email" title="<?= trans("email"); ?>" aria-label="Send via Email">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"></path>
            </svg>
        </a>
    </div>

    <div class="d-flex align-items-center right gap-2">
        <button type="button" id="btnPrintPost" class="btn-social-share btn-action-icon"
                data-bs-toggle="tooltip" title="<?= trans("print", "attr"); ?>" aria-label="<?= trans("print", "attr"); ?>">
            <svg viewBox="0 0 16 16" width="16" height="16" fill="currentColor">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/>
            </svg>
        </button>

        <?php if (authCheck()) :
            if ($isInReadingList == false) : ?>
                <button type="button" class="btn-social-share btn-action-icon btn-reading-list" aria-label="<?= trans("add_reading_list", "attr"); ?>"
                        onclick="toggleReadingListItem('<?= $post->id; ?>');" data-bs-toggle="tooltip" title="<?= trans("add_reading_list", "attr"); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16" fill="currentColor">
                        <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5zm2-1a1 1 0 0 0-1 1v12.566l4.723-2.482a.5.5 0 0 1 .554 0L13 14.566V2a1 1 0 0 0-1-1z"/>
                    </svg>
                </button>
            <?php else: ?>
                <button type="button" class="btn-social-share btn-reading-list active" aria-label="<?= trans("delete_reading_list", "attr"); ?>"
                        onclick="toggleReadingListItem('<?= $post->id; ?>');" data-bs-toggle="tooltip" title="<?= trans("delete_reading_list", "attr"); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16" fill="currentColor">
                        <path fill-rule="evenodd" d="M2 15.5V2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.74.439L8 13.069l-5.26 2.87A.5.5 0 0 1 2 15.5m8.854-9.646a.5.5 0 0 0-.708-.708L7.5 7.793 6.354 6.646a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0z"/>
                    </svg>
                </button>
            <?php endif;
        else:
            if (isset($config->registration_system) && $config->registration_system == 1): ?>
                <button type="button" class="btn-social-share btn-action-icon btn-reading-list"
                        aria-label="<?= trans("add_reading_list", "attr"); ?>" data-bs-toggle="tooltip" title="<?= trans("add_reading_list", "attr"); ?>"
                        onclick="var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('loginModal')); modal.show();">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16" fill="currentColor">
                        <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5zm2-1a1 1 0 0 0-1 1v12.566l4.723-2.482a.5.5 0 0 1 .554 0L13 14.566V2a1 1 0 0 0-1-1z"/>
                    </svg>
                </button>
            <?php endif;
        endif; ?>
    </div>
</div>