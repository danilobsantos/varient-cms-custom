<?php if (!empty($attachments)): ?>
    <div class="post-attachments">
        <div class="ps-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 15V3"/>
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <path d="m7 10 5 5 5-5"/>
            </svg>
            <span><?= trans("downloads"); ?>&nbsp;(<?= countItems($attachments); ?>)</span>
        </div>

        <div class="ps-grid">
            <?php foreach ($attachments as $attachment):
                $ext = pathinfo($attachment->file_name ?? '', PATHINFO_EXTENSION); ?>
                <form action="<?= base_url('download-file'); ?>" method="post">
                    <?= csrf_field(); ?>
                    <input type="hidden" name="id" value="<?= esc($attachment->id); ?>">

                    <button type="submit" name="file_type" value="attachment" class="ps-item" data-ext="<?= esc($ext, 'attr'); ?>">
                        <div class="ps-icon"></div>
                        <div class="ps-info">
                            <span class="ps-filename"><?= esc($attachment->file_name); ?></span>
                            <span class="ps-badge"><?= esc(strtoupper($ext)); ?></span>
                        </div>
                        <div class="ps-action">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 15V3"/>
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <path d="m7 10 5 5 5-5"/>
                            </svg>
                        </div>
                    </button>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>