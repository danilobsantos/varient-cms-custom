<?= $this->section('styles'); ?>
    <link href="<?= base_url('assets/vendor/quiz/quiz.css'); ?>" rel="stylesheet">
<?= $this->endSection(); ?>

<?php
$paywallType = !empty($premiumMembership->paywallAppearance) ? $premiumMembership->paywallAppearance : 'fade';
?>

    <div class="d-flex mb-4">
        <div class="quiz-container <?php echo ($post->post_format == 'poll') ? 'poll-mode' : ''; ?>"
             id="quiz_container"
             data-quiz-type="<?= $post->post_format; ?>"
             data-post-id="<?= $post->id; ?>"
             data-total-questions="<?= count($quizQuestions); ?>">

            <?php
            if (!$hasAccess && $paywallType === 'hard') {
                echo loadCommonView('premium/_paywall', [
                        'restrictionType' => $restrictionType,
                        'paywallType'     => 'hard'
                ]);
            } else {
                if (!$hasAccess && $paywallType === 'fade') {
                    echo '<div style="position: relative; overflow: hidden; min-height: 520px;">';
                }

                $itemCount = 1;
                $totalQuestions = count($quizQuestions);

                if (!empty($quizQuestions)):
                    foreach ($quizQuestions as $question):
                        $isLastQuestion = ($totalQuestions == $itemCount) ? '1' : '0';
                        $isPollPublic = isset($question->is_vote_public) ? $question->is_vote_public : '1';
                        $pollPublicAttr = ($post->post_format == 'poll') ? 'data-is-poll-public="' . $isPollPublic . '"' : '';
                        $cardModeClass = ($post->post_format == 'poll') ? 'poll-mode' : '';
                        ?>

                        <div id="quiz_question_<?= $question->id; ?>" class="quiz-card quiz-question <?= $cardModeClass; ?>"
                             data-question-id="<?= $question->id; ?>"
                             data-is-last-question="<?= $isLastQuestion; ?>"
                                <?= $pollPublicAttr; ?>>

                            <div class="quiz-header">
                                <div class="section-label"><?= trans("question"); ?> <?= $itemCount; ?> / <?= $totalQuestions; ?></div>
                                <h3 class="question-title"><?= esc($question->question); ?></h3>

                                <?php if (!empty($question->description)): ?>
                                    <div class="description font-text mt-2 text-muted"><?= $question->description; ?></div>
                                <?php endif; ?>

                                <?php if (!empty($question->image_default)): ?>
                                    <div class="question-image mt-3">
                                        <img src="<?= getStorageFileUrl($question->image_default, $question->storage); ?>"
                                             alt="<?= esc($question->question); ?>"
                                             class="img-fluid rounded w-100"
                                             width="856" height="570" loading="lazy"/>
                                    </div>
                                <?php endif; ?>

                                <?php if ($post->post_format == 'poll'): ?>
                                    <div class="small text-muted mt-2 total-votes-wrapper">
                                        <?= trans("total_votes"); ?>: <span id="question_total_votes_<?= $question->id; ?>">0</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="quiz-body">
                                <?php if (!$hasAccess && $paywallType === 'fade' && $itemCount === 1): ?>
                                <div class="pe-none">
                                    <?php endif; ?>

                                    <?php
                                    $questionAnswers = !empty($quizAnswers[$question->id]) ? $quizAnswers[$question->id] : [];

                                    if (!empty($questionAnswers)):
                                        $answerFormat = $question->answer_format;
                                        $isSmallImage = ($answerFormat == 'small_image');
                                        $isLargeImage = ($answerFormat == 'large_image');
                                        $isImageFormat = ($isSmallImage || $isLargeImage);

                                        $gridClass = '';
                                        if ($isSmallImage) {
                                            $gridClass = 'grid-cols-3';
                                        } elseif ($isLargeImage) {
                                            $gridClass = 'grid-cols-2';
                                        }

                                        if ($isImageFormat): ?>
                                            <div class="options-grid <?= $gridClass; ?>">
                                        <?php endif;

                                        foreach ($questionAnswers as $answer):
                                            $answerImageUrl = !empty($answer->image_small) ? getStorageFileUrl($answer->image_small, $answer->storage) : '';

                                            $dataAttrs = 'data-post-id="' . $post->id . '" ';
                                            $dataAttrs .= 'data-question-id="' . $question->id . '" ';
                                            $dataAttrs .= 'data-answer-id="' . $answer->id . '" ';
                                            $dataAttrs .= 'data-assigned-id="' . $answer->assigned_result_id . '" ';

                                            if ($isImageFormat):
                                                $dataAttrs .= 'data-format="image"';
                                                ?>
                                                <div id="question_answer_<?= $answer->id; ?>" class="option-card type-image answer" <?= $dataAttrs; ?>>
                                                    <div class="image-area">
                                                        <?php if (!empty($answerImageUrl)): ?>
                                                            <div class="bg-image" style="background-image: url('<?= esc($answerImageUrl); ?>');"></div>
                                                        <?php endif; ?>

                                                        <?php if ($post->post_format == 'poll'): ?>
                                                            <div class="progress-bar"></div>
                                                        <?php endif; ?>

                                                        <div class="check-overlay flex-column">
                                                            <i class="quiz-icon icon-circle mb-2"></i>

                                                            <?php if ($post->post_format == 'poll'): ?>
                                                                <span class="percent-text">0%</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="image-label-area"><?= esc($answer->answer_text); ?></div>
                                                </div>

                                            <?php

                                            else:
                                                $dataAttrs .= 'data-format="text"';
                                                ?>
                                                <div id="question_answer_<?= $answer->id; ?>" class="option-card type-text answer" <?= $dataAttrs; ?>>
                                                    <?php if ($post->post_format == 'poll'): ?>
                                                        <div class="progress-bar"></div>
                                                    <?php endif; ?>

                                                    <div class="content-wrapper">
                                                        <span class="text-label"><?= esc($answer->answer_text); ?></span>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <?php if ($post->post_format == 'poll'): ?>
                                                                <span class="percent-text" id="text_op_num_votes_<?= $answer->id; ?>">0%</span>
                                                            <?php endif; ?>

                                                            <i class="quiz-icon icon-circle"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif;
                                        endforeach;
                                        if ($isImageFormat): ?>
                                            </div>
                                        <?php endif;
                                    endif; ?>

                                    <?php if ($post->post_format == 'trivia_quiz'): ?>
                                        <div class="mt-3">
                                            <div class="alert alert-success" id="alert_correct_<?= $question->id; ?>" style="display:none;" role="alert">
                                                <h4 class="text m-0">
                                                    <?= trans("correct_answer"); ?>
                                                </h4>
                                            </div>
                                            <div class="alert alert-danger" id="alert_wrong_<?= $question->id; ?>" style="display:none;" role="alert">
                                                <h4 class="text m-0">
                                                    <?= trans("wrong_answer"); ?>
                                                </h4>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!$hasAccess && $paywallType === 'fade' && $itemCount === 1): ?>
                                </div>
                            <?php endif; ?>
                            </div>
                        </div>

                        <?php
                        if (!$hasAccess && $paywallType === 'fade' && $itemCount === 1) {
                            echo loadCommonView('premium/_paywall', [
                                    'restrictionType' => $restrictionType,
                                    'paywallType'     => 'fade'
                            ]);
                            break;
                        }

                        $itemCount++;
                    endforeach;
                endif;

                if (!$hasAccess && $paywallType === 'fade') {
                    echo '</div>';
                }
            }
            ?>

            <div class="row mt-4">
                <div class="col-sm-12">
                    <div id="quiz_result_container"></div>
                </div>

                <div class="col-sm-12 btn-play-again-content" style="display:none;">
                    <div class="d-flex justify-content-center align-items-center mt-3">
                        <button type="button" class="btn btn-lg btn-custom d-flex align-items-center btn-quiz-replay">
                            <?= trans("play_again"); ?>
                            <svg width="16" height="16" viewBox="0 0 1792 1792" fill="#ffffff" class="m-l-5" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1664 256v448q0 26-19 45t-45 19h-448q-42 0-59-40-17-39 14-69l138-138q-148-137-349-137-104 0-198.5 40.5t-163.5 109.5-109.5 163.5-40.5 198.5 40.5 198.5 109.5 163.5 163.5 109.5 198.5 40.5q119 0 225-52t179-147q7-10 23-12 15 0 25 9l137 138q9 8 9.5 20.5t-7.5 22.5q-109 132-264 204.5t-327 72.5q-156 0-298-61t-245-164-164-245-61-298 61-298 164-245 245-164 298-61q147 0 284.5 55.5t244.5 156.5l130-129q29-31 70-14 39 17 39 59z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?= $this->section('scripts'); ?>
    <script src="<?= base_url('assets/vendor/quiz/quiz.js'); ?>"></script>
<?= $this->endSection(); ?>