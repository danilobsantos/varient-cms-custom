<?php if (!empty($polls)): ?>
    <div id="sidebar-poll-widget" class="sidebar-widget">
        <div class="widget-head"><h4 class="title"><?= esc($widget->title); ?></h4></div>
        <div id="widget-polls-container" class="widget-body">
            <?php foreach ($polls as $poll):
                $totalVotes = 0;
                foreach ($poll['options'] as $opt) {
                    $totalVotes += $opt['votes'];
                } ?>
                <div class="poll-item-wrapper" style="visibility: hidden;">
                    <h5 class="poll-question"><?= esc($poll['question']) ?></h5>

                    <?php if ($poll['hasVoted']): ?>
                        <div class="results-list">
                            <?php foreach ($poll['options'] as $option):
                                $percent = $totalVotes > 0 ? round(($option['votes'] / $totalVotes) * 100) : 0; ?>
                                <div class="result-row">
                                    <div class="result-meta">
                                        <span><?= esc($option['text']) ?></span>
                                        <span><?= $percent ?>%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: <?= $percent ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="poll-footer-text">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon-svg me-1" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                                <?= trans('total_votes') ?>: <?= $totalVotes ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <div class="poll-options-list">
                            <?php foreach ($poll['options'] as $option): ?>
                                <div class="poll-option">
                                    <?= esc($option['text']) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-start mt-2">
                            <span class="poll-footer-text">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon-svg me-1" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                                <?= trans('total_votes') ?>: <?= $totalVotes ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

        </div>
    </div>
<?php endif; ?>

<script>
    window.SD_POLLS_DATA = <?= !empty($pollsJson) ? $pollsJson : '[]' ?>;
    window.SD_POLLS_LABELS = {
        totalVotes: "<?= trans('total_votes') ?>"
    };
</script>