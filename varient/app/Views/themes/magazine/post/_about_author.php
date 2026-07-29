<div class="d-flex about-author gap-3">
    <div class="flex-shrink-0">
        <a href="<?= generateProfileURL($postUser->slug); ?>" class="author-link">
            <img src="<?= getUserAvatar($postUser->avatar, $postUser->storage_avatar); ?>" alt="<?= esc($postUser->username); ?>" class="img-fluid img-author" width="100" height="100">
        </a>
    </div>
    <div class="flex-grow-1">
        <strong class="username"><a href="<?= generateProfileURL($postUser->slug); ?>"> <?= esc($postUser->username); ?> </a></strong>
        <p class="description">
            <?= esc($postUser->about_me); ?>
        </p>

        <?= view('themes/common/partials/_profile_social_links', ['user' => $postUser]); ?>
    </div>
</div>