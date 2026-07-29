<div class="col-12">
    <div class="sidebar-widget">
        <div class="widget-head">
            <h4 class="title"><?= trans('followers'); ?>&nbsp;(<?= countItems($followers); ?>)</h4>
        </div>
        <div class="widget-body">
            <div class="list-followers vr-scrollbar">
                <?php if (!empty($followers)):
                    foreach ($followers as $item):?>
                        <div class="img-follower">
                            <a href="<?= generateProfileURL($item->slug); ?>">
                                <img src="<?= getUserAvatar($item->avatar, $item->storage_avatar); ?>" alt="<?= esc($item->username); ?>" class="img-fluid" width="48" height="48" loading="lazy">
                            </a>
                        </div>
                    <?php endforeach;
                endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="col-12 mb-4">
    <div class="sidebar-widget">
        <div class="widget-head"><h4 class="title"><?= trans('following'); ?>&nbsp;(<?= countItems($following); ?>)</h4></div>
        <div class="widget-body">
            <div class="list-followers vr-scrollbar">
                <?php if (!empty($following)):
                    foreach ($following as $item):?>
                        <div class="img-follower">
                            <a href="<?= generateProfileURL($item->slug); ?>">
                                <img src="<?= getUserAvatar($item->avatar, $item->storage_avatar); ?>" alt="<?= esc($item->username); ?>" class="img-fluid" width="48" height="48" loading="lazy">
                            </a>
                        </div>
                    <?php endforeach;
                endif; ?>
            </div>
        </div>
    </div>
</div>