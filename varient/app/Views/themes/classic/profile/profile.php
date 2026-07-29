<?= $this->extend($viewsPath . '/layout'); ?>
<?= $this->section('content'); ?>

<section class="section section-page section-profile account-settings">
    <div class="container-fluid">
        <div class="row">
            <div class="profile-header <?= !empty($user->cover_image) ? 'has-image' : ''; ?>">

                <?php if (!empty($user->cover_image)): ?>
                    <div class="profile-cover-image" data-bg="<?= esc(base_url($user->cover_image ?? ''), 'attr'); ?>"></div>
                <?php endif; ?>

                <div class="profile-info-container">
                    <div class="container-xl position-relative">
                        <div class="d-flex align-items-end w-100 gap-3 profile-info">
                            <div class="profile-image">
                                <img src="<?= getUserAvatar($user->avatar, $user->storage_avatar); ?>" alt="<?= esc($user->username); ?>" width="140" height="140">
                            </div>

                            <div class="profile-username w-100">
                                <div class="d-flex align-items-center flex-wrap column-gap-2 row-gap-1 row-gap-md-2 mb-1">
                                    <h1 class="username m-0"><?= esc($user->username); ?></h1>
                                    <?php if (!empty($user->plan_id)): ?>
                                        <?= renderUserBadge(['plan_id' => $user->plan_id, 'lang_id' => $activeLang->id, 'solid' => 1]); ?>
                                    <?php else: ?>
                                        <?= !empty($user->badge_id) ? renderUserBadge(['badge_id' => $user->badge_id, 'lang_id' => $activeLang->id, 'solid' => 1]) : ''; ?>
                                    <?php endif; ?>
                                </div>

                                <div class="profile-last-seen<?= isUserOnline($user->last_seen) ? ' online' : ''; ?> mb-2 mb-md-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="icon-circle" viewBox="0 0 16 16">
                                        <circle cx="8" cy="8" r="8"/>
                                    </svg>
                                    <?= trans("last_seen_user"); ?>:&nbsp;<?= timeAgo($user->last_seen); ?>
                                </div>
                            </div>
                        </div>

                        <div class="container-profile-follow">
                            <?php if (authCheck()):
                                if (user()->id != $user->id): ?>
                                    <form action="<?= base_url('toggle-follow-post'); ?>" method="post">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="profile_id" value="<?= $user->id; ?>">
                                        <input type="hidden" name="back_url" value="<?= esc(current_url(true)); ?>">
                                        <?php if ($isUserFollows): ?>
                                            <button type="submit" class="btn btn-secondary btn-follow gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-dash-fill" viewBox="0 0 16 16">
                                                    <path fill-rule="evenodd" d="M11 7.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5z"/>
                                                    <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                                                </svg>
                                                <span class="d-none d-md-block"><?= trans("unfollow"); ?></span>
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-custom btn-follow gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16">
                                                    <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                                                    <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5z"/>
                                                </svg>
                                                <span class="d-none d-md-block"><?= trans("follow"); ?></span>
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                <?php endif;
                            else: ?>
                                <button type="submit" class="btn btn-custom btn-follow gap-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16">
                                        <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                                        <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5z"/>
                                    </svg>
                                    <span class="d-none d-md-block"><?= trans("follow"); ?></span>
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="container-xl container-profile">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-4 pt-2">
                <div class="sticky-lg-top">
                    <div class="row">
                        <div class="col-12">
                            <div class="profile-details">
                                <p class="description"><?= esc($user->about_me); ?></p>

                                <div class="d-flex flex-row mb-4 contact-details">
                                    <div class="item">
                                        <?= trans("member_since"); ?>&nbsp;<?= formatDateClient($user->created_at); ?>
                                    </div>
                                    <?php if ($config->show_user_email_on_profile == 1 && $user->show_email_on_profile == 1): ?>
                                        <div class="item profile-email">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-fill" viewBox="0 0 16 16">
                                                <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555ZM0 4.697v7.104l5.803-3.558L0 4.697ZM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586l-1.239-.757Zm3.436-.586L16 11.801V4.697l-5.803 3.546Z"/>
                                            </svg>
                                            &nbsp;<?= esc($user->email); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex flex-row mb-4">
                                    <?= view('themes/common/partials/_profile_social_links', ['user' => $user]); ?>
                                </div>
                            </div>
                        </div>

                        <?= loadView('profile/_followers'); ?>

                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-12 col-lg-8 pt-2">
                <div class="row">
                    <?php $i = 0;
                    if (!empty($posts)):
                        foreach ($posts as $item):
                            if ($i == 2):
                                echo loadCommonView('partials/_ad_spaces', ['adSpace' => 'posts_top', 'class' => 'mb-4']);
                            endif; ?>
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <?= loadView("post/_post_item", ['postItem' => $item, 'showLabel' => false]); ?>
                            </div>
                            <?php $i++;
                        endforeach;
                    endif;
                    echo loadCommonView('partials/_ad_spaces', ['adSpace' => 'posts_bottom', 'class' => 'mb-3']); ?>

                    <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
                        <div class="col-12 mt-3">
                            <?= $pager->links(); ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection(); ?>