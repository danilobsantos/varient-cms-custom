<?php

namespace App\Models;

class QuizQuestionModel extends BaseModel
{
    protected $table = 'quiz_questions';
    protected $allowedFields = ['post_id', 'question', 'image_id', 'description', 'question_order', 'answer_format'];
    protected $answerModel;

    public function __construct()
    {
        parent::__construct();

        $this->answerModel = model('QuizAnswerModel');
    }

    /**
     * Synchronizes questions and passes result mapping to answers
     */
    public function syncQuizQuestions(int $postId, array $questionsData, array $resultIdMapping = []): bool
    {
        $this->db->transStart();

        try {
            // Delete removed questions
            $this->processDeletions($postId, $questionsData);

            // Insert / Update questions
            foreach ($questionsData as $qKey => $qData) {
                $currentQuestionId = $this->saveSingleQuestion($postId, $qKey, $qData);
                // Sync answers
                if ($currentQuestionId && !empty($qData['answers']) && is_array($qData['answers'])) {
                    $correctAnswerKey = $qData['is_correct_answer_id'] ?? null;

                    $this->answerModel->syncQuizAnswers(
                        $currentQuestionId,
                        $qData['answers'],
                        $correctAnswerKey,
                        $resultIdMapping
                    );
                }
            }

            $this->db->transComplete();

        } catch (\Exception $e) {
            return false;
        }

        return $this->db->transStatus();
    }

    /**
     * Inserts a question
     */
    private function saveSingleQuestion(int $postId, $qKey, array $qData): int
    {
        $data = [
            'post_id' => $postId,
            'question' => $qData['question'] ?? '',
            'description' => $qData['description'] ?? '',
            'image_id' => !empty($qData['image_id']) ? $qData['image_id'] : null,
            'question_order' => $qData['question_order'] ?? 1,
            'answer_format' => $qData['answer_format'] ?? 'small_image',
        ];

        if (is_numeric($qKey)) {
            $this->update($qKey, $data);
            return (int)$qKey;
        }

        $this->insert($data);

        return (int)$this->getInsertID();
    }

    /**
     * Finds all questions associated with a specific post ID
     */
    public function findAllByPostId(int $postId): array
    {
        if (!$postId) {
            return [];
        }

        $subQuery = $this->builder()->select('*')->where('post_id', $postId);

        return $this->db->newQuery()
            ->fromSubquery($subQuery, 'qq')
            ->select('qq.*')
            ->select('i.image_default, i.image_small, i.storage')
            ->join('post_item_images i', 'i.id = qq.image_id', 'left')
            ->orderBy('qq.question_order', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Finds all question IDs associated with a specific post ID
     */
    public function getIdsByPostId(int $postId): array
    {
        if (!$postId) {
            return [];
        }

        $builder = $this->builder();
        $builder->resetQuery();

        $rows = $builder->select('id')
            ->where('post_id', $postId)
            ->orderBy('question_order', 'ASC')
            ->get()
            ->getResultArray();

        return array_column($rows, 'id');
    }

    /**
     * Handles the logic for finding and deleting removed questions
     */
    private function processDeletions(int $postId, array $questionsData): void
    {
        $existingIds = $this->where('post_id', $postId)->findColumn('id') ?? [];
        $incomingIds = array_filter(array_keys($questionsData), 'is_numeric');
        $idsToDelete = array_diff($existingIds, $incomingIds);

        if (!empty($idsToDelete)) {
            $this->answerModel->deleteByQuestionIds($idsToDelete);
            $this->delete($idsToDelete);
        }
    }

    /**
     * Deletes all questions for a specific post
     */
    public function deleteAllByPostId(int $postId): void
    {
        if ($postId <= 0) {
            return;
        }

        $ids = $this->where('post_id', $postId)->findColumn('id');

        if (!empty($ids)) {
            $this->db->transStart();

            $this->answerModel->deleteByQuestionIds($ids);
            $this->delete($ids);

            $this->db->transComplete();
        }
    }
}