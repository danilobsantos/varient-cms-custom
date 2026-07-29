<?php

namespace App\Models;

class PollOptionModel extends BaseModel
{
    protected $table = 'poll_options';
    protected $allowedFields = ['poll_id', 'option_text', 'votes'];
    protected $cacheGroup = 'app';

    /**
     * Syncs the poll options
     */
    public function saveOptions(int $pollId, array $data): void
    {
        $existingOptionIds = array_column($this->findAllByPollId($pollId) ?? [], 'id');

        $submittedTexts = $data['options'] ?? [];
        $submittedIds = $data['options_id'] ?? [];
        $processedIds = [];

        foreach ($submittedTexts as $key => $text) {
            if (empty(trim($text))) {
                continue;
            }

            $optionId = $submittedIds[$key] ?? null;

            if (!empty($optionId) && in_array($optionId, $existingOptionIds)) {
                $this->update($optionId, ['option_text' => $text]);
                $processedIds[] = $optionId;
            } else {
                $this->insert([
                    'poll_id'     => $pollId,
                    'option_text' => $text,
                    'votes'       => 0,
                ]);
            }
        }

        $optionsToDelete = array_diff($existingOptionIds, $processedIds);

        if (!empty($optionsToDelete)) {
            $this->whereIn('id', $optionsToDelete)->delete();
        }
    }

    /**
     * Retrieves all options associated with a specific poll ID
     */
    public function findAllByPollId(int $pollId): array
    {
        return $this->where('poll_id', $pollId)->findAll();
    }

    /**
     * Verifies if a specific option ID belongs to a given poll ID
     */
    public function optionBelongsToPoll(int $pollId, int $optionId): bool
    {
        if ($pollId <= 0 || $optionId <= 0) {
            return false;
        }

        return $this->select('1')
                ->where(['id' => $optionId, 'poll_id' => $pollId])
                ->first() !== null;
    }

    /**
     * Increments the vote count for a specific option by a given amount
     */
    public function incrementVotes(int $optionId, int $count = 1): bool
    {
        $option = $this->find($optionId);
        if ($option) {

            $newVotes = ((int)$option->votes) + $count;

            return $this->update($optionId, ['votes' => $newVotes]);
        }

        return false;
    }
}