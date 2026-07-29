<?php
$qType = $quizType ?? '';
$cCount = isset($correctCount) ? (int)$correctCount : 0;
$tQuestions = isset($totalQuestions) ? (int)$totalQuestions : 0;
$wCount = $tQuestions - $cCount;
if ($wCount < 0) {
    $wCount = 0;
}
$resultImageUrl = !empty($result->image_default) ? getStorageFileUrl($result->image_default, $result->storage) : '';
?>
<div class="quiz-result-card">
    <div class="result-content-pad">
        <?php if ($qType === 'trivia_quiz'): ?>
            <div class="result-stats-row">
                <div class="stat-pill correct">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <span class="stat-text">
                        <?= trans("correct_answer"); ?>: <b><?= $cCount; ?></b>
                    </span>
                </div>

                <div class="stat-pill wrong">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/>
                    </svg>
                    <span class="stat-text">
                        <?= trans("wrong_answer"); ?>: <b><?= $wCount; ?></b>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($result)): ?>
            <h3 class="result-title"><?= esc($result->result_title ?? ''); ?></h3>

            <?php if (!empty($resultImageUrl)): ?>
                <div class="result-image-wrapper">
                    <img src="<?= esc($resultImageUrl); ?>" alt="<?= esc($result->result_title ?? ''); ?>" class="result-image-full"/>
                </div>
            <?php endif; ?>

            <div class="result-description font-text">
                <?= $result->description ?? ''; ?>
            </div>
        <?php endif; ?>
    </div>
</div>