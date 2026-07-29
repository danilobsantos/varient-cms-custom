<?php if (!empty($widget) && $widget->status == 1):
    if ($widget->widget_type == 'follow-us'): ?>
        <?php $socialLinks = getSiteSocialLinks($baseSettings);
        if (!empty($socialLinks)):?>
            <div class="sidebar-widget">
                <div class="widget-head"><h4 class="title"><?= esc($widget->title); ?></h4></div>
                <div class="widget-body">
                    <div class="row gx-3 widget-follow">
                        <?php foreach ($socialLinks as $link):
                            if (!empty($link->url)):
                                $socialKey = $link->key ?? '';
                                $socialName = trans($socialKey) ?? ''; ?>
                                <div class="col-sm-3 col-md-6 item">
                                    <a href="<?= esc($link->url); ?>" target="_blank" rel="nofollow noopener" class="color-<?= esc($socialKey, "attr"); ?>" title="<?= esc($socialName, "attr"); ?>" style="background-color: <?= $link->color; ?>;">
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
            <div class="widget-body">
                <?= view_cell('App\Cells\RecommendedPosts', ['showcasePosts' => $showcasePosts]); ?>
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
    <?php endif;
endif; ?>