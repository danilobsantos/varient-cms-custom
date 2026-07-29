<div class="col-sidebar<?= (int)$config->sticky_sidebar === 1 ? ' sticky-lg-top' : ''; ?>">
    <div class="row">
        <div class="col-12">

            <?= loadCommonView('partials/_ad_spaces', ['adSpace' => 'sidebar_1', 'class' => '']); ?>

            <?php if (!empty($widgets)):
                foreach ($widgets as $widget): ?>

                    <?php if ($widget->widget_type == 'follow-us'):
                        $socialLinks = getSiteSocialLinks($baseSettings);
                        if (!empty($socialLinks)): ?>
                            <div class="sidebar-widget">
                                <div class="widget-head"><h4 class="title"><?= esc($widget->title); ?></h4></div>
                                <div class="widget-body">
                                    <div class="row gx-3 widget-follow">
                                        <?php foreach ($socialLinks as $link):
                                            $socialKey = $link->key ?? '';
                                            $socialName = trans($socialKey) ?? '';

                                            if (!empty($link->url)): ?>
                                                <div class="col-sm-3 col-md-6 item">
                                                    <a href="<?= esc($link->url); ?>" target="_blank" rel="nofollow noopener" class="color-<?= esc($socialKey, 'attr'); ?>" title="<?= esc($socialName, 'attr'); ?>" style="background-color: <?= $link->color; ?>;">
                                                        <?= $link->svg; ?>
                                                        <?= esc($socialName); ?>
                                                    </a>
                                                </div>
                                            <?php endif;
                                        endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif;
                    elseif ($widget->widget_type == 'popular-posts'): ?>
                        <div class="sidebar-widget">
                            <div class="widget-head"><h4 class="title"><?= esc($widget->title); ?></h4></div>
                            <div class="widget-body">
                                <?= view_cell('App\Cells\PopularPosts', ['langId' => (int)$activeLang->id]); ?>
                            </div>
                        </div>
                    <?php elseif ($widget->widget_type == 'recommended-posts'): ?>
                        <div class="sidebar-widget">
                            <div class="widget-head"><h4 class="title"><?= esc($widget->title); ?></h4></div>
                            <div class="widget-body recommended-posts">
                                <?= view_cell('App\Cells\RecommendedPosts', ['showcasePosts' => $showcasePosts, 'highlightFirst' => true]); ?>
                            </div>
                        </div>
                    <?php elseif ($widget->widget_type == 'tags'): ?>
                        <div class="sidebar-widget">
                            <div class="widget-head"><h4 class="title"><?= esc($widget->title); ?></h4></div>
                            <div class="widget-body">
                                <?= view_cell('App\Cells\PopularTags', ['langId' => (int)$activeLang->id]); ?>
                            </div>
                        </div>
                    <?php elseif ($widget->widget_type == 'poll'): ?>
                        <?= view_cell('App\Cells\Polls', [
                                'widget' => $widget,
                                'langId' => (int)$activeLang->id,
                                'userId' => authCheck() ? (int)user()->id : null
                        ]); ?>
                    <?php elseif ((int)$widget->is_custom === 1): ?>
                        <div class="sidebar-widget">
                            <div class="widget-head"><h4 class="title"><?= esc($widget->title); ?></h4></div>
                            <div class="widget-body">
                                <?= $widget->content; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php endforeach;
            endif; ?>

            <?= loadCommonView('partials/_ad_spaces', ['adSpace' => 'sidebar_2', 'class' => 'mb-4']); ?>

        </div>
    </div>
</div>