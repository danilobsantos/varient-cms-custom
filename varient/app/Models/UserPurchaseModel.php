<?php

namespace App\Models;

class UserPurchaseModel extends BaseModel
{
    protected $table = 'user_purchases';
    protected $allowedFields = ['user_id', 'post_id', 'order_id'];
    protected $createdField = 'created_at';

    /**
     * Checks if a specific user has already purchased a specific post
     */
    public function hasPurchased(int $userId, int $postId): bool
    {
        $purchase = $this->where('user_id', $userId)
            ->where('post_id', $postId)
            ->first();

        return !empty($purchase);
    }

    /**
     * Retrieves all single purchases made by a specific user
     */
    public function getUserPurchases(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Finds, filters, and paginates all user purchases
     */
    public function findAllPaginated(int $perPage, ?int $userId = null): array
    {
        $this->select('user_purchases.*, o.item_id as item_id, o.item_title as item_title, p.slug as item_slug')
            ->join('orders o', 'o.id = user_purchases.order_id', 'inner')
            ->join('posts p', 'p.id = user_purchases.post_id', 'left')
            ->where('o.item_type', 'content')
            ->where('o.status', 'completed');

        if (!empty($userId) && $userId > 0) {
            $this->where('user_purchases.user_id', $userId);
        }

        return $this->orderBy('user_purchases.id', 'DESC')
            ->paginate($perPage, 'purchases');
    }
}