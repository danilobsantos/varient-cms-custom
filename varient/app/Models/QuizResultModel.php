<?php

namespace App\Models;

class QuizResultModel extends BaseModel
{
    protected $table = 'quiz_results';
    protected $allowedFields = ['post_id', 'result_title', 'image_id', 'description', 'min_correct_count', 'max_correct_count', 'result_order'];

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
            ->fromSubquery($subQuery, 'qr')
            ->select('qr.*')
            ->select('i.image_default, i.image_small, i.storage')
            ->join('post_item_images i', 'i.id = qr.image_id', 'left')
            ->orderBy('qr.result_order', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Synchronizes (Adds, Updates, Deletes) quiz results and returns ID mapping
     */
    public function syncQuizResults(int $postId, array $resultsData): array
    {
        $this->db->transStart();
        $idMapping = [];

        try {
            // Delete removed results
            $this->processDeletions($postId, $resultsData);

            // Insert / Update results
            foreach ($resultsData as $rKey => $rData) {
                $data = [
                    'post_id'           => $postId,
                    'result_title'      => $rData['result_title'] ?? '',
                    'description'       => $rData['description'] ?? '',
                    'image_id'          => !empty($rData['image_id']) ? $rData['image_id'] : null,
                    'result_order'      => $rData['result_order'] ?? 1,
                    // Specific to Trivia Quizzes
                    'min_correct_count' => isset($rData['min_correct_count']) && $rData['min_correct_count'] !== '' ? (int)$rData['min_correct_count'] : null,
                    'max_correct_count' => isset($rData['max_correct_count']) && $rData['max_correct_count'] !== '' ? (int)$rData['max_correct_count'] : null,
                ];

                $realId = null;

                if (is_numeric($rKey)) {
                    // Update existing result
                    $this->update($rKey, $data);
                    $realId = (int)$rKey;
                } else {
                    // Insert new result (Keys starting with 'r_...')
                    $this->insert($data);
                    $realId = $this->getInsertID();
                }

                // Record the mapping: (e.g., 'r_abc123' => 15)
                $idMapping[$rKey] = $realId;
            }

            $this->db->transComplete();

        } catch (\Exception $e) {
            return ['success' => false, 'id_mapping' => []];
        }

        return [
            'success'    => $this->db->transStatus(),
            'id_mapping' => $idMapping
        ];
    }

    /**
     * Handles the logic for finding and deleting removed results
     */
    private function processDeletions(int $postId, array $resultsData): void
    {
        $existingIds = $this->where('post_id', $postId)->findColumn('id') ?? [];
        $incomingIds = array_filter(array_keys($resultsData), 'is_numeric');
        $idsToDelete = array_diff($existingIds, $incomingIds);

        if (!empty($idsToDelete)) {
            $this->delete($idsToDelete);
        }
    }

    /**
     * Deletes all results for a specific post
     */
    public function deleteAllByPostId(int $postId): void
    {
        if ($postId <= 0) {
            return;
        }

        $this->db->transStart();

        $this->where('post_id', $postId)->delete();

        $this->db->table('post_poll_votes')->where('post_id', $postId)->delete();

        $this->db->transComplete();
    }
}