<?= $this->extend($viewsPath . '/layout'); ?>
<?= $this->section('content'); ?>

    <section id="postDetailsPage" class="section section-page" data-id="<?= $post->id; ?>">
        <div class="container-xl">
            <div class="row<?= $isFullWidth ? ' justify-content-center' : ''; ?>">

                <?php if (!$isFullWidth): ?>
                    <?= loadCommonView('post/_breadcrumb_post'); ?>
                <?php endif; ?>

                <div class="col-md-12 <?= $isFullWidth ? 'col-lg-9' : 'col-lg-8'; ?>">
                    <div class="post-content">
                        <div class="d-flex justify-content-center align-items-center mb-3">
                            <div class="bd-highlight">
                                <a href="<?= generateCategoryUrl($post); ?>">
                                    <span class="badge badge-category" style="background-color: <?= esc($post->cat_color); ?>"><?= esc($post->cat_name); ?></span>
                                </a>

                                <?php if ((int)$post->status !== 1 || (int)$post->visibility !== 1 || (int)$post->is_scheduled === 1): ?>
                                    <span class="badge badge-category bg-danger"><?= trans("preview"); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="bd-highlight ms-auto">
                                <?php if (authCheck() && (hasPermission('manage_all_posts') || user()->id == $post->user_id)): ?>
                                    <a href="<?= adminUrl('posts/edit/' . $post->id); ?>" class="btn btn-xs btn-warning gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                            <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
                                        </svg><?= trans("edit"); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <h1 class="post-title"><?= esc($post->title); ?></h1>
                        <?php if (!empty($post->summary) && empty($post->feed_id)): ?>
                            <h2 class="post-summary">
                                <?= esc($post->summary); ?>
                            </h2>
                        <?php endif; ?>

                        <div class="d-flex flex-column flex-md-row align-items-md-center gap-2 gap-md-3 post-details-meta mb-3">
                            <?php if ($config->show_post_author == 1): ?>
                                <div class="item-meta item-meta-author flex-shrink-0">
                                    <a href="<?= generateProfileURL($postUser->slug); ?>" class="d-flex align-items-center gap-2">
                                        <img src="<?= getUserAvatar($postUser->avatar, $postUser->storage_avatar); ?>" alt="<?= esc($postUser->username); ?>" width="32" height="32" class="rounded-circle object-fit-cover">
                                        <span><?= esc($postUser->username); ?></span>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3 column-gap-3 w-100 flex-grow-1">
                                <?php if ($config->show_post_date == 1): ?>
                                    <div class="item-meta item-meta-date d-inline-flex align-items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-clock" viewBox="0 0 16 16">
                                            <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                        </svg>
                                        <span><?= formatDateClient($post->created_at); ?>&nbsp;-&nbsp;<?= formatHour($post->created_at); ?></span>
                                    </div>
                                    <?php if (!empty($post->updated_at)): ?>
                                        <div class="item-meta item-meta-date d-inline-flex align-items-center">
                                            <span><?= trans("updated"); ?>:&nbsp;<?= timeAgo($post->updated_at); ?></span>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <div class="ms-md-auto d-inline-flex item-meta item-meta-counters gap-2">
                                    <?php if ($config->comment_system == 1): ?>
                                        <span class="d-flex align-items-center gap-1">
                                            <i class="vr-icon vri-comments"></i>
                                            <span><?= numberFormatShort($post->comment_count); ?></span>
                                        </span>
                                    <?php endif;
                                    if ($config->show_pageviews): ?>
                                        <span class="d-flex align-items-center gap-1">
                                            <i class="vr-icon vri-eye"></i>
                                            <span><?= numberFormatShort($post->pageviews); ?></span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex post-share-buttons mb-3">
                            <?= loadCommonView('post/_post_share'); ?>
                        </div>
                        <?php if ($post->post_format == 'video'):
                            echo loadCommonView('post/_video_player', ['post' => $post]);
                        elseif ($post->post_format == 'audio'):
                            echo loadCommonView('post/_music_player', ['post' => $post]);
                        elseif ($post->post_format == 'gallery'):
                            echo loadCommonView('post/_gallery', ['post' => $post]);
                        elseif ($post->post_format == 'sorted_list'):
                            echo loadCommonView('post/_sorted_list', ['post' => $post]);
                        elseif ($post->post_format == 'table_of_contents'):
                            echo loadCommonView('post/_table_of_contents', ['post' => $post]);
                        elseif ($post->post_format == 'trivia_quiz' || $post->post_format == 'personality_quiz'):
                            echo view('themes/common/quiz/_quiz', ['post' => $post]);
                        elseif ($post->post_format == 'poll'):
                            echo view('themes/common/quiz/_poll', ['post' => $post]);
                        elseif ($post->post_format == 'recipe'):
                            echo loadCommonView('post/_recipe', ['post' => $post]);
                        elseif ($post->post_format == 'event'):
                            echo loadCommonView('post/_event', ['post' => $post]);
                        else:
                            echo loadCommonView('post/_post_image_preview', ['post' => $post, 'postImages' => $postImages]);
                        endif;
                        echo loadCommonView('partials/_ad_spaces', ['adSpace' => 'post_top', 'class' => '']);
                        if (!empty($post->content) && $post->post_format != 'recipe' && $post->post_format != 'event'):?>
                            <div class="entry-content mt-4">
                                <?= loadCommonView('post/_post_content'); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($hasAccess):
                            if (!empty($post->faq) && !in_array($post->post_format, ['trivia_quiz', 'personality_quiz', 'poll', 'event'])):
                                echo loadCommonView('post/_faq', ['post' => $post]);
                            endif;

                            if (!empty($post->optional_url)) : ?>
                                <div class="d-flex flex-row-reverse mt-4">
                                    <a href="<?= esc($post->optional_url); ?>" class="btn btn-custom btn-icon" target="_blank" rel="nofollow">
                                        <?= esc($baseSettings->optional_url_button_name); ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="m-l-5" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                                        </svg>
                                    </a>
                                </div>
                            <?php endif;
                            if (!empty($feed) && !empty($post->show_post_url)) : ?>
                                <div class="d-flex flex-row-reverse mt-4">
                                    <a href="<?= $post->post_url; ?>" class="btn btn-custom" target="_blank" rel="nofollow">
                                        <?= esc($feed->read_more_button_text); ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="m-l-5" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                                        </svg>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?= loadCommonView('post/_attachments', ['post' => $post, 'attachments' => $attachments]); ?>

                            <?php if (!empty($postTags)): ?>
                            <div class="d-flex flex-row post-tags align-items-center mt-5">
                                <?php if (!empty($postTags)): ?>
                                    <h2 class="title"><?= trans("post_tags"); ?></h2>
                                    <ul class="d-flex flex-row">
                                        <?php foreach ($postTags as $tag) : ?>
                                            <li><a href="<?= generateTagURL($tag->tag_slug); ?>"><?= esc($tag->tag); ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php endif; ?>

                        <div id="postNextPrevContainer" class="post-next-prev mt-5">
                            <?= loadView('post/details/_adjacent_posts', ['adjacentPosts' => $adjacentPosts]); ?>
                        </div>

                        <?php if ($config->emoji_reactions == 1): ?>
                            <div class="row">
                                <div class="col-sm-12">
                                    <?= view('themes/common/partials/_emoji_reactions', ['postReactions' => $postReactions]); ?>
                                </div>
                            </div>
                        <?php endif;

                        if (!empty($postUser) && $config->show_post_author == 1):
                            echo loadView('post/_about_author', ['post' => $post, 'postUser' => $postUser]);
                        endif;

                        echo loadCommonView('partials/_ad_spaces', ['adSpace' => 'post_bottom', 'class' => '']); ?>
                        <section class="section section-related-posts mt-5">
                            <div class="row">
                                <div class="col-12">
                                    <div class="section-title">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h3 class="title"><?= trans("related_posts"); ?></h3>
                                        </div>
                                    </div>
                                    <div class="section-content">
                                        <div class="row">
                                            <?php $i = 0;
                                            if (!empty($relatedPosts) && countItems($relatedPosts) > 0):
                                                shuffle($relatedPosts);
                                                foreach ($relatedPosts as $item):
                                                    if ($post->id != $item->id):
                                                        if ($i < 6):
                                                            if ($i > 0 && $i % 3 == 0): ?>
                                                                <div class="col-sm-12 col-md-12"></div>
                                                            <?php endif; ?>
                                                            <div class="col-sm-12 col-md-6 col-lg-4">
                                                                <div class="post-item<?= checkPostImg($item, 'class'); ?>">
                                                                    <?php if (checkPostImg($item)): ?>
                                                                        <div class="image ratio">
                                                                            <a href="<?= generatePostUrl($item); ?>"<?= postUrlNewTab($item); ?>>
                                                                                <img src="<?= getPostImageUrl($item, 'mid'); ?>" alt="<?= esc(!empty($item->alt_text) ? $item->alt_text : $item->title); ?>" class="img-fluid" width="269" height="160" loading="lazy"/>
                                                                                <?= getMediaIcon($item); ?>
                                                                            </a>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <h3 class="title"><a href="<?= generatePostUrl($item); ?>"<?= postUrlNewTab($this, $item); ?>><?= esc(characterLimiter($item->title, POST_DISPLAY_TITLE_LIMIT, '...')); ?></a></h3>
                                                                    <?= loadView('post/_post_meta', ['postItem' => $item]); ?>
                                                                </div>
                                                            </div>
                                                        <?php endif;
                                                        $i++;
                                                    endif;
                                                endforeach;
                                            endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <?php if ((int)$config->comment_system === 1): ?>
                            <?= loadCommonView('comments/_comments'); ?>
                        <?php endif; ?>

                    </div>
                </div>

                <?php if (!$isFullWidth): ?>
                    <div class="col-md-12 col-lg-4">
                        <?= loadView('partials/_sidebar'); ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section>

    <div id="post-tracker-data" data-post-id="<?= $post->id; ?>" data-time-spent="<?= esc($postTimeSpent); ?>" class="d-none"></div>

<?= $this->endSection(); ?>