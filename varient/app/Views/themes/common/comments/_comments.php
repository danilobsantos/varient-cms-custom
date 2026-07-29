<?php
$totalCommentsCount = $commentCounts->total_count ?? 0;
$rootCommentsCount = $commentCounts->root_count ?? 0;
$initialCount = isset($comments) ? countItems($comments) : 0;
$isLoggedIn = authCheck();
$currentUser = $isLoggedIn ? user() : null;
$defaultAvatar = base_url(AVATAR_GUEST);
$currentUserAvatar = $defaultAvatar;
if ($isLoggedIn && !empty($currentUser->avatar)) {
    $currentUserAvatar = getStorageFileUrl($currentUser->avatar, $currentUser->storage_avatar);
} ?>

    <section class="section comments-module">
        <div class="row">
            <div class="col-12">
                <div class="section-title">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="title">
                            <?= trans("comments", true); ?>&nbsp;(<span id="totalCommentsCount"><?= numberFormatShort($totalCommentsCount); ?></span>)
                        </h3>
                    </div>
                </div>

                <div class="section-content">
                    <div class="modern-editor-wrapper<?= (!$isLoggedIn) ? ' guest-mode' : '' ?>">
                        <form id="mainCommentForm" novalidate>
                            <input type="hidden" name="form_load_time" value="<?= time(); ?>">

                            <div class="d-flex align-items-start">
                                <div class="avatar-container pt-1 flex-shrink-0">
                                    <img src="<?= esc($currentUserAvatar); ?>" class="avatar-img" width="38" height="38" alt="User">
                                </div>

                                <div class="flex-grow-1 ms-3" style="min-width: 0;">
                                    <div class="editor-container" id="mainEditorContainer">
                                        <textarea class="editor-textarea w-100" id="commentContent" placeholder="<?= trans("write_a_comment", "attr"); ?>" required></textarea>

                                        <?php if (!$isLoggedIn): ?>
                                            <div class="guest-details-row d-flex flex-column flex-sm-row gap-2 mt-2">
                                                <input type="text" class="input-minimal w-100" id="guestName" placeholder="<?= trans("name", "attr"); ?>" required>
                                                <input type="email" class="input-minimal w-100" id="guestEmail" placeholder="<?= trans("email", "attr"); ?>" required>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-footer-actions mt-3">
                                        <div class="d-flex justify-content-end w-100">
                                            <button type="submit" class="btn btn-custom btn-post-comment" id="submitBtn">
                                                <?= trans("post_comment", true); ?> <i class="vr-icon vri-send ms-1"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <?php if ($totalCommentsCount > 2): ?>
                        <div class="d-flex justify-content-end align-items-center">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link fw-bold dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-filter-left me-1" viewBox="0 0 16 16">
                                        <path d="M2 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m0-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m0-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/>
                                    </svg>
                                    <span id="sortBtnText"><?= trans("newest_first", true); ?></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-sort-comments shadow-sm border-0 mt-2">
                                    <li>
                                        <button type="button" class="dropdown-item sort-option py-2" data-sort="newest"><?= trans("newest_first", true); ?></button>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item sort-option py-2" data-sort="oldest"><?= trans("oldest_first", true); ?></button>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item sort-option py-2" data-sort="popular"><?= trans("popular", true); ?></button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div id="commentsList">
                        <?php if (!empty($comments)):
                            foreach ($comments as $comment): ?>
                                <?= loadCommonView('comments/_comment', ['comment' => $comment, 'level' => 1]) ?>
                            <?php endforeach;
                        endif; ?>
                    </div>

                    <button class="load-more-btn" id="loadMoreBtn" style="<?= ($rootCommentsCount <= $initialCount) ? 'display:none;' : '' ?>">
                        <span class="btn-text"><?= trans("load_more_comments", true); ?></span>
                        <svg class="btn-icon-svg" width="16" height="16" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z" fill="currentColor"/>
                        </svg>
                    </button>

                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="captchaModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-dialog-captcha">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0 justify-content-center pt-4">
                    <h6 class="modal-title fw-bold fs-5 text-heading"><?= trans("security_check", true); ?></h6>
                </div>

                <div class="modal-body px-4 pb-4 pt-2 text-center">
                    <p class="text-muted small mb-3"><?= trans("security_check_exp", true); ?></p>

                    <div id="captchaContainer" class="modal-captcha-container">
                        <div class="spinner-border captcha-spinner" role="status">
                            <span class="visually-hidden"></span>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-2">
                        <button type="button" class="btn btn-primary fw-bold py-2" id="verifyCaptchaBtn">
                            <?= trans("verify_post_comment", true); ?>
                        </button>
                        <button type="button" class="btn btn-light text-muted fw-semibold py-2" data-bs-dismiss="modal">
                            <?= trans("cancel", true); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?= $this->section('scripts'); ?>
    <script>
        const VRCommentConfig = {
            endpoints: {
                load: '<?= base_url("comments/load") ?>',
                add: '<?= base_url("comments/add") ?>',
                delete: '<?= base_url("comments/delete") ?>',
                like: '<?= base_url("comments/like") ?>'
            },
            postId: <?= $post->id ?? 0 ?>,
            perPage: <?= defined('COMMENT_LIMIT') ? COMMENT_LIMIT : 6; ?>,
            csrfTokenName: '<?= csrf_token() ?>',
            csrfHash: '<?= csrf_hash() ?>',
            isLoggedIn: <?= authCheck() ? 'true' : 'false' ?>,
            initialPage: <?= $initialCount > 0 ? 2 : 1 ?>,
            captchaStatus: <?= (int)($config->captcha_settings->status ?? 0); ?>,
            hasAccess: <?= !empty($hasAccess) ? 'true' : 'false' ?>,
            restrictionType: '<?= esc($restrictionType) ?>',
            text: {
                ok: <?= json_encode(trans("ok")); ?>,
                yes: <?= json_encode(trans("yes")); ?>,
                cancel: <?= json_encode(trans("cancel")); ?>,
                confirmComment: <?= json_encode(trans("confirm_comment")); ?>,
                botVerificationFailed: <?= json_encode(trans("bot_verification_failed")); ?>
            },
        };
    </script>
    <script src="<?= base_url('assets/common/js/comments.min.js') ?>"></script>
<?= $this->endSection(); ?>