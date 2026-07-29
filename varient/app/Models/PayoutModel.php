<?php

namespace App\Models;

class PayoutModel extends BaseModel
{
    protected $table = 'payouts';
    protected $allowedFields = ['user_id', 'username', 'email', 'amount', 'payout_method', 'status', 'created_at'];
    protected $createdField = 'created_at';

    /**
     * Creates a new payout
     */
    public function create(array $postData, ?object $user, ?bool $approve = false): bool
    {
        if (!$user && !empty($user->id)) {
            return false;
        }

        $data = [
            'user_id'       => $user->id,
            'username'      => $user->username ?? '',
            'email'         => $user->email ?? '',
            'amount'        => (double)($postData['amount'] ?? 0),
            'payout_method' => $postData['payout_method'] ?? '',
            'status'        => 0,
        ];

        $this->db->transStart();

        if ($this->insert($data)) {
            $insertId = $this->db->insertID();

            if (!empty($approve)) {
                $this->approve($insertId);
            }
        }

        $this->db->transComplete();

        return (bool)$this->db->transStatus();
    }

    /**
     * Approves a payout
     */
    public function approve(int $id): bool
    {
        $payout = $this->find($id);
        if (!$payout || (int)$payout->status !== 0) {
            return false;
        }

        $this->db->transStart();

        $this->update($payout->id, ['status' => 1]);

        // Update user balance
        $userModel = new UserModel();
        $userModel->updateBalance($payout->user_id, $payout->amount);

        $this->db->transComplete();

        return (bool)$this->db->transStatus();
    }

    /**
     * Finds active payputs by user ID
     */
    public function getUserPayouts(int $userId, bool $onlyActive = true): array
    {
        if ($onlyActive) {
            $this->where('status', 0);
        }

        return $this->where('user_id', $userId)
            ->orderBy('id DESC')
            ->findAll();
    }

    /**
     * Finds, filters, and paginates payouts
     */
    public function findAllPaginated(array $filters, int $perPage): array
    {
        $builder = $this->select('payouts.*');

        if (!empty($filters['user_id'])) {
            $builder->where('user_id', (int)$filters['user_id']);
        }

        if (!empty($filters['q'])) {
            $builder->groupStart()
                ->like('username', $filters['q'])
                ->orLike('email', $filters['q'])
                ->orLike('payout_method', $filters['q'])
                ->groupEnd();
        }

        return $builder->orderBy('created_at DESC')
            ->paginate($perPage);
    }
}