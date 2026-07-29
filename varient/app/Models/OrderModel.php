<?php

namespace App\Models;

class OrderModel extends BaseModel
{
    protected $table = 'orders';
    protected $allowedFields = ['order_token', 'user_id', 'item_type', 'item_id', 'item_title', 'billing_cycle', 'payment_method_id', 'payment_method', 'currency', 'subtotal', 'tax_rate', 'tax',
        'transaction_fee_rate', 'transaction_fee', 'total_amount', 'billing_name', 'billing_email', 'billing_address', 'billing_city', 'billing_country', 'billing_zip_code', 'billing_company_name',
        'billing_tax_number', 'payment_url', 'gateway_plan_id', 'ip_address', 'status'];
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Inserts a new order record into the database
     */
    public function create(array $data): int|false
    {
        if (!$this->insert($data)) {
            return false;
        }

        return (int)$this->getInsertID();
    }

    /**
     * Retrieves a pending order by its token
     */
    public function getOrderByToken(string $token): ?object
    {
        return $this->where('order_token', $token)
            ->first();
    }

    /**
     * Deletes records that do NOT have a 'completed' status and were created more than 5 days ago
     */
    public function deleteOldIncompleteOrders(): int|false
    {
        try {
            $thresholdDate = \CodeIgniter\I18n\Time::now()->subDays(5)->toDateTimeString();

            $this->where('status !=', 'completed')
                ->where('created_at <=', $thresholdDate)
                ->delete();

            return $this->db->affectedRows();

        } catch (\Exception $e) {
            return false;
        }
    }
}