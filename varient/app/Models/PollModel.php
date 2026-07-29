<?php

namespace App\Models;

class PollModel extends BaseModel
{
    protected $table = 'polls';
    protected $allowedFields = ['lang_id', 'question', 'status', 'vote_permission', 'created_at'];
    protected $createdField = 'created_at';
    protected $pollOptionModel;
    protected $cacheGroup = 'app';

    protected $validationRules = [
        'question' => [
            'label' => 'trans:question',
            'rules' => 'required'
        ]
    ];

    public function __construct()
    {
        parent::__construct();

        $this->pollOptionModel = model('PollOptionModel');
    }

    /**
     * Creates a new poll record in the database and saves associated options
     */
    public function create(array $data): bool
    {
        if ($this->insert($data)) {

            $pollId = $this->db->insertID();

            $this->pollOptionModel->saveOptions($pollId, $data);

            return true;
        }

        return false;
    }

    /**
     * Updates an existing poll record and its options based on the provided data
     */
    public function updatePoll(object $poll, array $data): bool
    {
        if (!$poll) return false;

        if ($this->update($poll->id, $data)) {

            $this->pollOptionModel->saveOptions($poll->id, $data);

            return true;
        }
        return false;
    }

    /**
     * Retrieves all active polls for a specific language ID
     */
    public function findAllByLangId(int $langId): array
    {
        return $this->where('lang_id', $langId)->where('status', 1)
            ->orderBy('polls.id DESC')
            ->findAll();
    }

    /**
     * Retrieves active polls and their options
     */
    public function getPollsWithOptions(int $langId): array
    {
        $polls = $this->where('lang_id', $langId)
            ->where('status', 1)
            ->orderBy('id', 'DESC')
            ->findAll();

        $baseData = [];

        foreach ($polls as $poll) {
            $optionsRaw = $this->pollOptionModel->findAllByPollId($poll->id);

            $optionsFormatted = [];
            foreach ($optionsRaw as $opt) {
                $optionsFormatted[] = [
                    'id'    => (int)$opt->id,
                    'text'  => $opt->option_text,
                    'votes' => (int)$opt->votes
                ];
            }

            $baseData[] = [
                'id'              => (int)$poll->id,
                'question'        => $poll->question,
                'vote_permission' => $poll->vote_permission,
                'options'         => $optionsFormatted
            ];
        }

        return $baseData;
    }

    /**
     * Retrieves a paginated list of polls with optional filters for language, status, and search query
     */
    public function findAllPaginated(array $filters, int $perPage): array
    {
        $builder = $this->select('polls.*, languages.name as language_name')
            ->join('languages', 'languages.id = polls.lang_id', 'left');

        if (!empty($filters['lang_id'])) {
            $builder->where('polls.lang_id', (int)$filters['lang_id']);
        }
        if (isset($filters['status'])) {
            $status = $filters['status'] == 'inactive' ? 0 : 1;
            $builder->where('polls.status', $status);
        }
        if (!empty($filters['q'])) {
            $builder->like('polls.question', cleanStr($filters['q']));
        }

        return $builder->orderBy('polls.id DESC')
            ->paginate($perPage);
    }

    /**
     * Deletes a poll and all its associated options from the database
     */
    public function deletePoll(int $id): bool
    {
        $poll = $this->find($id);

        if (!$poll) return false;

        $this->db->transStart();

        $this->pollOptionModel->where('poll_id', $id)->delete();

        model('PollVoteModel')->where('poll_id', $id)->delete();

        $this->where('id', $id)->delete();

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}