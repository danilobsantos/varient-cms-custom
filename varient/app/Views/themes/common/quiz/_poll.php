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
                        $totalVotes = 0;
                        $userAnswerId = getPollQuestionAnswerByUser($userPollAnswers, $question->id);

                        if (empty($userAnswerId)) {
                            $sessionKey = 'poll_v_' . $question->id;
                            $sessionVal = getSession($sessionKey);
                            if (!empty($sessionVal)) {
                                $userAnswerId = $sessionVal;
                            }
                        }

                        $isLastQuestion = ($totalQuestions == $itemCount) ? '1' : '0';

                        if (!empty($quizAnswers[$question->id])) {
                            foreach ($quizAnswers[$question->id] as $answer) {
                                $totalVotes += $answer->total_votes;
                            }
                        }

                        $hasVoted = !empty($userAnswerId);
                        $questionStateClass = $hasVoted ? 'quiz-question-answered voted' : '';

                        $isPollPublic = isset($post->is_poll_public) ? $post->is_poll_public : '1';
                        $pollPublicAttr = ($post->post_format == 'poll') ? 'data-is-poll-public="' . $isPollPublic . '"' : '';
                        $cardModeClass = ($post->post_format == 'poll') ? 'poll-mode' : '';
                        ?>

                        <div id="quiz_question_<?= $question->id; ?>"
                             class="quiz-card quiz-question <?= $cardModeClass; ?> <?= $questionStateClass; ?>"
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

                                            $answerPercentage = 0;
                                            if ($totalVotes > 0) {
                                                $answerPercentage = round(($answer->total_votes / $totalVotes) * 100);
                                            }

                                            $isSelected = ($userAnswerId == $answer->id);
                                            $selectedClass = $isSelected ? 'selected' : '';
                                            $disabledClass = $hasVoted ? 'disabled' : '';

                                            $iconClass = $isSelected ? 'icon-check' : 'icon-circle';

                                            $modalAttributes = '';
                                            if ($post->post_format == 'poll' && !authCheck() && $isPollPublic == 0) {
                                                $modalAttributes = 'data-bs-toggle="modal" data-bs-target="#loginModal"';
                                            }

                                            $dataAttrs = 'data-post-id="' . $post->id . '" ';
                                            $dataAttrs .= 'data-question-id="' . $question->id . '" ';
                                            $dataAttrs .= 'data-answer-id="' . $answer->id . '" ';
                                            $dataAttrs .= 'data-assigned-id="' . $answer->assigned_result_id . '" ';
                                            $dataAttrs .= 'data-percent="' . $answerPercentage . '" ';

                                            if ($isImageFormat):
                                                $dataAttrs .= 'data-format="image"';
                                                ?>
                                                <div id="question_answer_<?= $answer->id; ?>"
                                                     class="option-card type-image answer <?= $selectedClass; ?> <?= $disabledClass; ?>"
                                                        <?= $dataAttrs; ?>
                                                        <?= $modalAttributes; ?>>

                                                    <div class="image-area">
                                                        <?php if (!empty($answerImageUrl)): ?>
                                                            <div class="bg-image" style="background-image: url('<?= esc($answerImageUrl); ?>');"></div>
                                                        <?php endif; ?>

                                                        <?php if ($post->post_format == 'poll'): ?>
                                                            <div class="progress-bar" style="width: <?= $hasVoted ? $answerPercentage : 0; ?>%"></div>
                                                        <?php endif; ?>

                                                        <div class="check-overlay flex-column">
                                                            <i class="quiz-icon <?= $iconClass; ?> mb-2"></i>

                                                            <?php if ($post->post_format == 'poll'): ?>
                                                                <span class="percent-text"><?= $answerPercentage; ?>%</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="image-label-area"><?= esc($answer->answer_text); ?></div>
                                                </div>

                                            <?php
                                            else:
                                                $dataAttrs .= 'data-format="text"';
                                                ?>
                                                <div id="question_answer_<?= $answer->id; ?>"
                                                     class="option-card type-text answer <?= $selectedClass; ?> <?= $disabledClass; ?>"
                                                        <?= $dataAttrs; ?>
                                                        <?= $modalAttributes; ?>>

                                                    <?php if ($post->post_format == 'poll'): ?>
                                                        <div class="progress-bar" style="width: <?= $hasVoted ? $answerPercentage : 0; ?>%"></div>
                                                    <?php endif; ?>

                                                    <div class="content-wrapper">
                                                        <span class="text-label me-2"><?= esc($answer->answer_text); ?></span>

                                                        <div class="d-flex align-items-center gap-3 flex-shrink-0">

                                                            <?php if ($post->post_format == 'poll'): ?>
                                                                <span class="percent-text" id="text_op_num_votes_<?= $answer->id; ?>"><?= $answerPercentage; ?>%</span>
                                                            <?php endif; ?>

                                                            <i class="quiz-icon <?= $iconClass; ?>"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif;
                                        endforeach;

                                        if ($isImageFormat): ?>
                                            </div>
                                        <?php endif;
                                    endif; ?>

                                    <?php if ($post->post_format == 'poll'): ?>
                                        <div class="small text-muted mt-4 text-start total-votes-wrapper">
                                            <b><?= trans("total_votes"); ?>: <span id="question_total_votes_<?= $question->id; ?>"><?= $totalVotes; ?></span></b>
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
        </div>
    </div>

<?= $this->section('scripts'); ?>
    <script src="<?= base_url('assets/vendor/quiz/quiz.js'); ?>"></script>
<?= $this->endSection(); ?>