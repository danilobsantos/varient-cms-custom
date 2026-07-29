<?php

namespace App\Services;

/**
 * A service class responsible for managing order process
 */
class OrderService
{
    /** @var object Application-level config object */
    protected object $config;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = getContextValue('config');
    }

    /**
     * Create a new pending order with country-based tax calculation
     *
     * @param array $postData
     * @param object $user
     * @param object $gateway
     * @param object $productData
     * @return int|string
     */
    public function createOrder(array $postData, object $user, object $gateway, object $productData): int|string
    {
        $orderModel = model('OrderModel');
        $countryModel = model('CountryModel');

        $countryId = (int)($postData['country_id'] ?? 0);
        $countryName = '';
        $taxRate = 0;

        // Fetch country and determine tax rate
        if ($countryId > 0) {
            $country = $countryModel->find($countryId);
            if (!empty($country)) {
                $taxRate = numToDecimal($country->vat_rate ?? 0);
                $countryName = $country->name ?? '';
            }
        }

        // Calculate Base Subtotal and Tax Amount
        $subtotal = numToDecimal($productData->price ?? 0);
        $taxAmount = numToDecimal($subtotal * ($taxRate / 100));
        $totalAmount = numToDecimal($subtotal + $taxAmount);

        // Calculate Transaction Fee (Surcharge added to total)
        $feeRate = numToDecimal($gateway->transaction_fee_rate ?? 0);
        $feeAmount = 0;

        if ($feeRate > 0 && $feeRate <= 100) {
            $feeAmount = numToDecimal($totalAmount * ($feeRate / 100));
            $totalAmount = numToDecimal($totalAmount + $feeAmount);
        }

        // Prepare Order Data Array
        $orderData = [
            'order_token'          => generateUuidV4() ?: uniqid(),
            'user_id'              => $user->id,
            'item_type'            => $productData->item_type ?? '',
            'item_id'              => $productData->item_id ?? 0,
            'item_title'           => $productData->title ?? '',
            'billing_cycle'        => $productData->billing_cycle ?? '',
            'payment_method_id'    => $gateway->id ?? 0,
            'payment_method'       => $gateway->name ?? '',
            'currency'             => $this->config->currency_settings->code ?? 'USD',
            'subtotal'             => $subtotal,
            'tax_rate'             => $taxRate,
            'tax'                  => $taxAmount,
            'transaction_fee_rate' => $feeRate,
            'transaction_fee'      => $feeAmount,
            'total_amount'         => $totalAmount,
            'billing_name'         => $postData['full_name'] ?? '',
            'billing_email'        => $postData['email'] ?? '',
            'billing_address'      => $postData['address'] ?? '',
            'billing_city'         => $postData['city_state'] ?? '',
            'billing_country'      => $countryName,
            'billing_zip_code'     => $postData['zip_postal_code'] ?? '',
            'billing_company_name' => $postData['company_name'] ?? '',
            'billing_tax_number'   => $postData['tax_vat_number'] ?? '',
            'ip_address'           => request()->getIPAddress(),
            'status'               => 'pending',
        ];

        return $orderModel->create($orderData);
    }

    /**
     * Get valid product data as an object based on provided item type and ID
     *
     * @param string $itemType
     * @param int $itemId
     * @param int $langId
     * @return object|null
     */
    public function getProductData(string $itemType, int $itemId, int $langId): ?object
    {
        if ($itemType === 'subscription') {
            $plan = model('SubscriptionPlanModel')->getActivePlan($itemId);
            if (!empty($plan)) {
                return (object)[
                    'item_type'          => 'subscription',
                    'item_id'            => $plan->id,
                    'title'              => getLocalizedObjectValue($plan->plan_name, $langId, 'name'),
                    'price'              => $plan->price,
                    'billing_cycle'      => $plan->billing_cycle,
                    'billing_cycle_text' => trans($plan->billing_cycle)
                ];
            }
        } elseif ($itemType === 'content') {
            $post = model('PostModel')->find($itemId);
            if (!empty($post)) {
                $price = 0;

                // Check category price first
                $category = model('CategoryModel')->find($post->category_id);
                if (!empty($category) && (int)$category->is_exclusive === 1) {
                    $price = $category->exclusive_price;
                }

                // If the post has its own specific price, it overrides the category price
                if ($post->exclusive_price > 0) {
                    $price = $post->exclusive_price;
                }

                return (object)[
                    'item_type'          => 'content',
                    'item_id'            => $post->id,
                    'title'              => $post->title,
                    'price'              => $price,
                    'billing_cycle'      => 'one_time_payment',
                    'billing_cycle_text' => trans("one_time_payment")
                ];
            }
        }

        return null;
    }
}