/**
 * Quiz Manager (Refactored & Encapsulated)
 * Handles logic for Trivia, Personality, and Poll quizzes.
 * Uses CSS Masks for icons to prevent FOUC.
 */
(function ($) {
    'use strict';

    const QuizManager = {
        /**
         * Configuration & Selectors
         */
        cfg: {
            container: '.quiz-container',
            resultContainer: '#quiz_result_container',
            endpoints: {
                checkTrivia: VrConfig.baseUrl + 'Ajax/checkTriviaQuizAnswer',
                getResult: VrConfig.baseUrl + 'Ajax/getQuizResult',
                addPollVote: VrConfig.baseUrl + 'Ajax/addPostPollVote'
            }
        },

        /**
         * Internal State
         */
        state: {
            quizType: '',
            postId: 0,
            answeredQuestions: [],
            userAnswers: [],
            correctCount: 0,
            totalQuestions: 0
        },

        /**
         * Initialization
         */
        init: function () {
            const $container = $(this.cfg.container).first();

            if ($container.length === 0) return;

            // Handle "Play Again" scroll logic
            if (sessionStorage.getItem('do_scroll')) {
                sessionStorage.removeItem('do_scroll');
                setTimeout(() => {
                    $('html, body').animate({
                        scrollTop: $container.offset().top - 100
                    }, 500);
                }, 100);
            }

            this.state.quizType = $container.attr('data-quiz-type');
            this.state.postId = $container.attr('data-post-id');
            this.state.totalQuestions = parseInt($container.attr('data-total-questions')) || 0;

            this.bindEvents();
        },

        /**
         * Event Binding
         */
        bindEvents: function () {
            const self = this;

            $(document).on('click', '.quiz-question .answer', function () {
                const $btn = $(this);
                const $question = $btn.closest('.quiz-question');
                const questionId = $question.attr('data-question-id');

                if ($question.hasClass('quiz-question-answered') || $question.hasClass('is-processing') || self.state.answeredQuestions.includes(questionId)) {
                    return;
                }

                $question.addClass('is-processing');
                $question.find('.answer').addClass('disabled');

                switch (self.state.quizType) {
                    case 'personality_quiz':
                        self.handlePersonality($btn, questionId);
                        break;
                    case 'poll':
                        self.handlePoll($btn, questionId);
                        break;
                    default:
                        self.handleTrivia($btn, questionId);
                }
            });

            $(document).on('click', '.btn-quiz-replay', function () {
                sessionStorage.setItem('do_scroll', '1');
                window.location.reload();
            });
        },

        /**
         * Polls
         */
        handlePoll: function ($btn, questionId) {
            const self = this;
            const answerId = $btn.attr('data-answer-id');
            const $question = $('#quiz_question_' + questionId);
            const isPublic = $question.attr('data-is-poll-public');

            if (typeof VrConfig.authCheck !== 'undefined' && VrConfig.authCheck != 1 && isPublic == 0) {
                $('#modalLogin').modal('show');

                $question.removeClass('is-processing');
                $question.find('.answer').removeClass('disabled');
                return;
            }

            $btn.css('opacity', '0.7');

            $.ajax({
                type: 'POST',
                url: self.cfg.endpoints.addPollVote,
                data: {question_id: questionId, answer_id: answerId},
                success: function (response) {
                    $btn.css('opacity', '1');

                    if (response.result === 1) {
                        // Mark selected
                        $btn.addClass('selected');

                        // Update Icon (Class Swap for Mask)
                        $btn.find('.quiz-icon')
                            .removeClass('icon-circle')
                            .addClass('icon-check');

                        // Mark question as voted
                        $question.addClass('quiz-question-answered voted');
                        $question.find('.answer').addClass('disabled');

                        self.state.answeredQuestions.push(questionId);

                        // Update Results & Scroll
                        self.updatePollVisuals(questionId, response);
                        self.scrollToNext();
                    } else {
                        $question.removeClass('is-processing');
                        $question.find('.answer').removeClass('disabled');
                    }
                },
                error: function (err) {
                    console.error(err);
                    $btn.css('opacity', '1');

                    $question.removeClass('is-processing');
                    $question.find('.answer').removeClass('disabled');
                }
            });
        },

        updatePollVisuals: function (questionId, data) {
            $('#question_total_votes_' + questionId).text(data.totalVotes);

            if (data.arrayVotes) {
                data.arrayVotes.forEach(vote => {
                    const $container = $('#quiz_question_' + questionId).find('.answer[data-answer-id="' + vote.answerId + '"]');

                    if ($container.length) {
                        $container.find('.progress-bar').css('width', vote.percentage + '%');
                        const $text = $container.find('.percent-text');
                        $text.text(vote.percentage + '%');

                        $({Counter: 0}).animate({Counter: vote.percentage}, {
                            duration: 1000,
                            easing: 'swing',
                            step: function () {
                                $text.text(Math.ceil(this.Counter) + "%");
                            }
                        });
                        // Legacy support
                        $('#text_op_num_votes_' + vote.answerId).text(vote.percentage + '%');
                    }
                });
            }
        },

        /**
         * Trivia
         */
        handleTrivia: function ($btn, questionId) {
            const self = this;
            const answerId = $btn.attr('data-answer-id');
            const $question = $('#quiz_question_' + questionId);

            $btn.css('opacity', '0.7');

            $.ajax({
                type: 'POST',
                url: self.cfg.endpoints.checkTrivia,
                data: {question_id: questionId, answer_id: answerId},
                success: function (response) {
                    $btn.css('opacity', '1');

                    if (response.result === 1) {
                        if (response.is_correct == 1) {
                            self.state.correctCount++;
                            self.uiSetStatus($btn, true);
                            $('#alert_correct_' + questionId).fadeIn();
                        } else {
                            self.uiSetStatus($btn, false);
                            // Highlight correct answer
                            const $correctBtn = $question.find('.answer[data-answer-id="' + response.correct_answer_id + '"]');
                            self.uiSetStatus($correctBtn, true);
                            $('#alert_wrong_' + questionId).fadeIn();
                        }

                        self.finalizeQuestion(questionId);
                    } else {
                        $question.removeClass('is-processing');
                        $question.find('.answer').removeClass('disabled');
                    }
                },
                error: function () {
                    $btn.css('opacity', '1');

                    $question.removeClass('is-processing');
                    $question.find('.answer').removeClass('disabled');
                }
            });
        },

        uiSetStatus: function ($btn, isCorrect) {
            const $icon = $btn.find('.quiz-icon');
            $icon.removeClass('icon-circle'); // Remove default

            if (isCorrect) {
                $btn.addClass('correct');
                $icon.addClass('icon-check');
            } else {
                $btn.addClass('incorrect');
                $icon.addClass('icon-cross');
            }
        },

        /**
         * Personality
         */
        handlePersonality: function ($btn, questionId) {
            const assignedId = $btn.attr('data-assigned-id');
            this.state.userAnswers.push(assignedId);

            $btn.addClass('selected');
            $btn.find('.quiz-icon')
                .removeClass('icon-circle')
                .addClass('icon-check');

            this.finalizeQuestion(questionId);
        },

        /**
         * Shared Utilities
         */
        finalizeQuestion: function (questionId) {
            const $question = $('#quiz_question_' + questionId);
            $question.addClass('quiz-question-answered');
            $question.find('.answer').addClass('disabled');

            this.state.answeredQuestions.push(questionId);

            if (this.state.answeredQuestions.length >= this.state.totalQuestions) {
                if (this.state.quizType !== 'poll') {
                    this.fetchResults();
                } else {
                    this.scrollToNext();
                }
            } else {
                this.scrollToNext();
            }
        },

        scrollToNext: function () {
            let targetId = "quiz_result_container";
            let foundUnanswered = false;

            $(".quiz-question").each(function () {
                const qId = $(this).attr('data-question-id');
                if (!$(this).hasClass('quiz-question-answered') && !QuizManager.state.answeredQuestions.includes(qId)) {
                    targetId = $(this).attr('id');
                    foundUnanswered = true;
                    return false;
                }
            });

            if (this.state.quizType === 'poll' && !foundUnanswered) {
                return;
            }

            if (targetId && $("#" + targetId).length) {
                setTimeout(function () {
                    $('html, body').animate({
                        scrollTop: $("#" + targetId).offset().top - 100
                    }, 600);
                }, 700);
            }
        },

        fetchResults: function () {
            const self = this;

            setTimeout(function () {
                const $resultContainer = $(self.cfg.resultContainer);

                $('html, body').animate({
                    scrollTop: $resultContainer.offset().top - 100
                }, 600);

                $resultContainer.html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

                const data = {
                    post_id: self.state.postId,
                    quiz_type: self.state.quizType
                };

                if (self.state.quizType === 'trivia_quiz') {
                    data.correct_count = self.state.correctCount;
                } else {
                    data.user_answers = self.state.userAnswers;
                }

                $.ajax({
                    type: 'POST',
                    url: self.cfg.endpoints.getResult,
                    data: data,
                    success: function (response) {
                        if (response.result === 1) {
                            $resultContainer.html(response.html);
                            $('.btn-play-again-content').show();
                        } else {
                            $resultContainer.html('<div class="alert alert-danger">Error loading results.</div>');
                        }
                    },
                    error: function () {
                        $resultContainer.html('<div class="alert alert-danger">Connection error.</div>');
                    }
                });
            }, 1000);
        }
    };

    $(document).ready(function () {
        QuizManager.init();
    });

})(jQuery);