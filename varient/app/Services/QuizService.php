<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Class QuizService
 *
 * This service is responsible for performing and managing quiz-related actions
 * including synchronization, result evaluation, and poll voting.
 */
class QuizService
{
    /**
     * @var object Model for managing quiz questions
     */
    protected $quizQuestionModel;

    /**
     * @var object Model for managing quiz answers
     */
    protected $quizAnswerModel;

    /**
     * @var object Model for managing quiz results
     */
    protected $quizResultModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->quizQuestionModel = model('QuizQuestionModel');
        $this->quizAnswerModel = model('QuizAnswerModel');
        $this->quizResultModel = model('QuizResultModel');
    }

    /**
     * Synchronizes quiz data (results, questions, and answers) for a specific post
     *
     * @param array $postData The submitted data containing 'results' and 'questions'.
     * @param int   $postId   The ID of the post associated with the quiz.
     * @return void
     */
    public function syncQuizData(array $postData, int $postId): void
    {
        $resultIdMapping = [];

        // Sync results
        $rawResults = $postData['results'] ?? null;
        if (empty($rawResults)) {
            $this->quizResultModel->deleteAllByPostId($postId);
        } else {
            // SyncQuizResults returns an array: ['success' => bool, 'id_mapping' => array]
            $syncOutput = $this->quizResultModel->syncQuizResults($postId, $rawResults);
            if (!empty($syncOutput['success']) && !empty($syncOutput['id_mapping'])) {
                $resultIdMapping = $syncOutput['id_mapping'];
            }
        }

        // Sync questions and answers
        $rawQuestions = $postData['questions'] ?? null;
        if (empty($rawQuestions)) {
            $this->quizQuestionModel->deleteAllByPostId($postId);
        } else {
            $this->quizQuestionModel->syncQuizQuestions($postId, $rawQuestions, $resultIdMapping);
        }
    }

    /**
     * Evaluates the user's answers and prepares the quiz result HTML
     *
     * @param array $data Contains keys: 'post_id', 'quiz_type', 'correct_count', 'user_answers'.
     * @return array Returns an array with status ('result') and the rendered 'html'.
     */
    public function prepareQuizResult(array $data): array
    {
        $postId = $data['post_id'] ?? null;
        $quizType = $data['quiz_type'] ?? null;
        $correctCount = $data['correct_count'] ?? 0;
        $userAnswers = $data['user_answers'] ?? []; // Array of assigned_result_ids

        if (empty($postId) || empty($quizType)) {
            return ['result' => 0, 'error' => 'Invalid input'];
        }

        $results = $this->quizResultModel->findAllByPostId($postId);
        $finalResult = null;

        $viewData = [
            'quizType'       => $quizType,
            'correctCount'   => 0,
            'totalQuestions' => 0
        ];

        // Trivia quiz
        if ($quizType == 'trivia_quiz') {

            // Get total question count for the score display
            $questions = $this->quizQuestionModel->getIdsByPostId($postId);
            $totalQuestions = count($questions);

            $viewData['correctCount'] = $correctCount;
            $viewData['totalQuestions'] = $totalQuestions;

            // Find the result based on score ranges
            if (!empty($results)) {
                // Default to the first result if no range matches
                $finalResult = $results[0];

                foreach ($results as $result) {
                    if ($correctCount >= $result->min_correct_count && $correctCount <= $result->max_correct_count) {
                        $finalResult = $result;
                        break;
                    }
                }
            }
        } // Personality quiz
        elseif ($quizType == 'personality_quiz') {

            if (!empty($userAnswers) && is_array($userAnswers) && !empty($results)) {
                // Count frequency of each result ID ['result_id_1' => 3, 'result_id_2' => 1]
                $counts = array_count_values($userAnswers);

                // Sort by frequency (descending)
                arsort($counts);

                // Get the ID with the highest frequency
                $mostFrequentResultId = array_key_first($counts);

                // Find the corresponding result object
                foreach ($results as $result) {
                    if ($result->id == $mostFrequentResultId) {
                        $finalResult = $result;
                        break;
                    }
                }

                // If no match found, use the first result
                if (!$finalResult) {
                    $finalResult = $results[0];
                }
            }
        }

        $viewData['result'] = $finalResult;
        $htmlContent = view('themes/common/quiz/_quiz_result', $viewData);

        return [
            'result' => 1,
            'html'   => $htmlContent
        ];
    }

    /**
     * Processes a user's vote for a poll question
     *
     * @param array $data Contains keys: 'question_id', 'answer_id'.
     * @return array Returns status ('result'), total votes, updated percentages, or an error message.
     */
    public function addPostPollVote(array $data): array
    {
        $questionId = isset($data['question_id']) ? (int)$data['question_id'] : 0;
        $answerId = isset($data['answer_id']) ? (int)$data['answer_id'] : 0;

        if (empty($questionId) || empty($answerId)) {
            return ['result' => 0, 'error' => 'Invalid input'];
        }

        $userId = (int)(authCheck() ? user()->id : 0);

        $question = $this->quizQuestionModel->find($questionId);
        if (!$question) {
            return ['result' => 0, 'error' => 'Question not found'];
        }

        $post = model('PostModel')->find($question->post_id);
        if (!$post) {
            return ['result' => 0, 'error' => 'Invalid'];
        }

        $isPublic = isset($post->is_poll_public) ? $post->is_poll_public : 1;
        if ($userId == 0 && $isPublic == 0) {
            return ['result' => 0, 'error' => 'Login required'];
        }

        $sessionKey = 'poll_v_' . $questionId;
        if (getSession($sessionKey)) {
            return ['result' => 0, 'error' => 'Already voted'];
        }

        if ($userId > 0) {
            $hasVoted = $this->quizAnswerModel->getVote($questionId, $userId);
            if ($hasVoted) {
                return ['result' => 0, 'error' => 'Already voted'];
            }
        }

        $isSuccess = $this->quizAnswerModel->handlePollVote((int)$question->post_id, $questionId, $answerId, $userId);
        if (!$isSuccess) {
            return ['result' => 0, 'error' => 'Database transaction failed'];
        }

        setSession($sessionKey, (string)$answerId);

        $allAnswers = $this->quizAnswerModel->where('question_id', $questionId)->findAll();

        $totalQuestionVotes = 0;
        foreach ($allAnswers as $ans) {
            $totalQuestionVotes += $ans->total_votes;
        }

        $arrayVotes = [];
        foreach ($allAnswers as $ans) {
            if ($totalQuestionVotes > 0) {
                $percentage = round(($ans->total_votes / $totalQuestionVotes) * 100);
            } else {
                $percentage = 0;
            }

            $arrayVotes[] = [
                'answerId'   => $ans->id,
                'totalVotes' => $ans->total_votes,
                'percentage' => $percentage
            ];
        }

        return [
            'result'     => 1,
            'totalVotes' => $totalQuestionVotes,
            'arrayVotes' => $arrayVotes
        ];
    }
}