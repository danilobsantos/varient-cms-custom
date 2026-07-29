<?php

namespace App\Models;

class PollVoteModel extends BaseModel
{
    protected $table = 'poll_votes';
    protected $allowedFields = ['poll_id', 'option_id', 'user_id', 'created_at'];
    protected $createdField = 'created_at';

    /**
     * Registers a new vote
     */
    public function create(int $pollId, int $optionId, ?int $userId)
    {
        // Check if the user has already voted
        if ($this->hasVoted($pollId, $userId)) {
            return false;
        }

        $pollOptionModel = model('PollOptionModel');

        $data = [
            'poll_id'   => $pollId,
            'option_id' => $optionId,
            'user_id'   => !empty($userId) ? $userId : 0
        ];

        if ($this->insert($data)) {
            $insertId = $this->db->insertID();

            $votedPolls = getSession('poll_votes') ?? [];

            $votedPolls[$pollId] = $optionId;

            setSession('poll_votes', $votedPolls);

            $pollOptionModel->incrementVotes($optionId);

            return $insertId;
        }

        return false;
    }

    /**
     * Checks if a user or guest has already voted
     */
    public function hasVoted(int $pollId, ?int $userId): bool
    {
        // Check by User ID
        if (!empty($userId)) {
            $userVote = $this->where('poll_id', $pollId)
                ->where('user_id', $userId)
                ->first();

            if ($userVote) {
                return true;
            }
        }

        // Check by Session (For guests and current browser session)
        $session = session();
        $votedPolls = $session->get('poll_votes') ?? [];

        if (isset($votedPolls[$pollId])) {
            return true;
        }

        return false;
    }

    /**
     * Retrieves the option ID the user voted for
     */
    public function getUserVote(int $pollId, ?int $userId): int
    {
        // Try to find vote by User ID
        if (!empty($userId)) {
            $vote = $this->where('poll_id', $pollId)
                ->where('user_id', $userId)
                ->first();
            if ($vote) {
                return (int)$vote->option_id;
            }
        }

        // Try to find vote by Session
        $votedPolls = getSession('poll_votes') ?? [];

        if (isset($votedPolls[$pollId])) {
            return (int)$votedPolls[$pollId];
        }

        return 0;
    }

    /**
     * Retrieves user votes for a specific list of polls in a single batch
     */
    public function getVotesForPolls(array $pollIds, int $userId): array
    {
        if (empty($pollIds)) {
            return [];
        }

        $results = $this->whereIn('poll_id', $pollIds)
            ->where('user_id', $userId)
            ->findAll();

        // Map the result to [poll_id => option_id] for constant-time O(1) lookups in loops
        $map = [];
        foreach ($results as $row) {
            $map[$row->poll_id] = $row->option_id;
        }

        return $map;
    }
}