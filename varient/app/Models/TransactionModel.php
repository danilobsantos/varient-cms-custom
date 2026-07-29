<?php

namespace App\Models;

class TransactionModel extends BaseModel
{
    protected $table = 'transactions';
    protected $allowedFields = ['order_id', 'user_id', 'gateway_transaction_id', 'payment_method', 'currency', 'total_amount', 'status'];
    protected $createdField = 'created_at';

    /**
     * Insert a new transaction record into the database
     */
    public function create(array $data): int|false
    {
        if (!$this->insert($data)) {
            return false;
        }

        return (int)$this->getInsertID();
    }

    /**
     * Retrieves the latest transactions for the dashboard widget
     */
    public function getLatestTransactions(int $limit = 5): array
    {
        return $this->select('transactions.*, u.username as user_username, u.slug as user_slug, o.order_token, o.item_type, o.item_id, o.item_title')
            ->join('users u', 'u.id = transactions.user_id', 'left')
            ->join('orders o', 'o.id = transactions.order_id', 'left')
            ->orderBy('transactions.id', 'DESC')
            ->limit($limit)
            ->get()->getResult();
    }

    /**
     * Finds, filters, and paginates all transactions
     */
    public function findAllPaginated(array $filters, int $perPage, ?int $userId = null): array
    {
        $builder = $this->select('transactions.*, u.username as user_username, u.slug as user_slug, o.order_token as order_token, o.item_type as item_type, o.item_id as item_id, o.item_title as item_title')
            ->join('users u', 'u.id = transactions.user_id', 'left')
            ->join('orders o', 'o.id = transactions.order_id', 'left');

        if (!empty($userId) && $userId > 0) {
            $builder->where('transactions.user_id', $userId);
        } else {
            if (!empty($filters['q'])) {
                $q = $filters['q'];
                $builder->groupStart()
                    ->like('transactions.gateway_transaction_id', $q)
                    ->orLike('o.id', $q)
                    ->orLike('o.item_title', $q)
                    ->orLike('o.order_token', $q)
                    ->orLike('u.username', $q)
                    ->groupEnd();
            }

            if (!empty($filters['transaction_type'])) {
                $builder->where('o.item_type', $filters['transaction_type']);
            }

            if (!empty($filters['payment_method'])) {
                $builder->where('transactions.payment_method', $filters['payment_method']);
            }

            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $builder->where('transactions.created_at >=', cleanStr($filters['start_date']) . ' 00:00:00')
                    ->where('transactions.created_at <=', cleanStr($filters['end_date']) . ' 23:59:59');
            }
        }

        return $builder->orderBy('transactions.id', 'DESC')
            ->paginate($perPage);
    }
}