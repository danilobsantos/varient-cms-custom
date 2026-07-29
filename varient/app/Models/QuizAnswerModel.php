<?php

namespace App\Models;

class QuizAnswerModel extends BaseModel
{
    protected $table = 'quiz_answers';
    protected $allowedFields = ['question_id', 'answer_text', 'image_id', 'is_correct', 'assigned_result_id', 'total_votes'];

    /**
     * Synchronizes answers and resolves assigned result IDs
     */
    public function syncQuizAnswers(int $questionId, array $answersData, ?string $correctAnswerKey, array $resultIdMapping = []): void
    {
        $existingIds = $this->where('question_id', $questionId)->findColumn('id') ?? [];
        $incomingIds = [];

        foreach ($answersData as $aKey => $aData) {
            // Strict comparison for correct answer radio button
            $isCorrect = ((string)$aKey === (string)$correctAnswerKey) ? 1 : 0;

            $assignedResultId = $aData['assigned_result_id'] ?? null;

            // If the assigned ID is a Temp ID (e.g., r_654abc), find the real ID from the map
            if (!empty($assignedResultId) && !is_numeric($assignedResultId)) {
                if (isset($resultIdMapping[$assignedResultId])) {
                    $assignedResultId = $resultIdMapping[$assignedResultId];
                } else {
                    $assignedResultId = null;
                }
            }

            $data = [
                'question_id'        => $questionId,
                'answer_text'        => $aData['answer_text'] ?? '',
                'image_id'           => !empty($aData['image_id']) ? $aData['image_id'] : null,
                'is_correct'         => $isCorrect,
                'assigned_result_id' => $assignedResultId
            ];

            if (is_numeric($aKey)) {
                // Update existing answer
                $this->update($aKey, $data);
                $incomingIds[] = (int)$aKey;
            } else {
                // Insert new answer
                $this->insert($data);
            }
        }

        // Delete removed answers
        $idsToDelete = array_diff($existingIds, $incomingIds);
        if (!empty($idsToDelete)) {
            $this->delete($idsToDelete);
        }
    }

    /**
     * Finds all questions associated with a specific post ID
     */
    public function findAllByPostId(int $postId, bool $group = false): array
    {
        if (!$postId) {
            return [];
        }

        $questionIds = model('QuizQuestionModel')->getIdsByPostId($postId);

        if (empty($questionIds)) {
            return [];
        }

        $subQuery = $this->builder()->whereIn('question_id', $questionIds);

        $rows = $this->db->newQuery()
            ->fromSubquery($subQuery, 'qa')
            ->select('qa.*')->select('i.image_default, i.image_small, i.storage')
            ->join('post_item_images i', 'i.id = qa.image_id', 'left')
            ->orderBy('qa.id', 'ASC')
            ->get()->getResult();

        if ($group) {
            return $this->groupByQuestion($rows);
        }

        return $rows;
    }

    /**
     * Groups an array of answer rows by their question_id
     */
    private function groupByQuestion(?array $rows): array
    {
        if (empty($rows)) {
            return [];
        }

        $groupData = [];

        foreach ($rows as $row) {
            $groupData[$row->question_id][] = $row;
        }

        return $groupData;
    }

    /**
     * Finds correct answer by question ID
     */
    public function findCorrectAnswer(int $questionId): ?object
    {
        return $this->where('question_id', $questionId)
            ->where('is_correct', 1)
            ->first();
    }

    /**
     * Finds all poll votes with a specific user ID
     */
    public function findAllPollVotesByUserId(int $postId, int $userId): ?array
    {
        return $this->db->table('post_poll_votes')
            ->where('user_id', $userId)
            ->where('post_id', $postId)
            ->get()
            ->getResult();
    }

    /**
    * Retrieves the vote record for a specific user and question
    */
    public function getVote(int $questionId, int $userId): ?object
    {
        if ($questionId <= 0 || $userId <= 0) {
            return null;
        }

        return $this->db->table('post_poll_votes')
            ->where('question_id', $questionId)
            ->where('user_id', $userId)
            ->get()
            ->getRow();
    }

    /**
     * Increases the vote count and logs the user vote in a transaction
     */
    public function handlePollVote(int $postId, int $questionId, int $answerId, int $userId): bool
    {
        $this->db->transStart();

        $this->builder()->where('id', $answerId)->increment('total_votes');

        $this->db->table('post_poll_votes')->insert([
            'post_id'     => $postId,
            'question_id' => $questionId,
            'answer_id'   => $answerId,
            'user_id'     => $userId
        ]);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Bulk delete answers by question IDs
     */
    public function deleteByQuestionIds(array $questionIds): void
    {
        if (empty($questionIds)) {
            return;
        }

        $this->whereIn('question_id', $questionIds)
            ->delete();
    }
}