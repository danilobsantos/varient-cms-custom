<?php

namespace App\Models;

class PaymentGatewayModel extends BaseModel
{
    protected $table = 'payment_gateways';
    protected $allowedFields = ['public_key', 'secret_key', 'webhook_secret', 'environment', 'transaction_fee_rate', 'base_product_id', 'status'];

    /**
     * Retrieves gateways
     */
    public function getGateways(bool $onlyActive = false): array
    {
        if ($onlyActive) {
            $this->where('status', 1);
        }
        return $this->orderBy('id')->findAll();
    }

    /**
     * Retrieves a gateway by name key
     */
    public function getGatewayByNameKey(string $nameKey): ?object
    {
        return $this->where('name_key', cleanStr($nameKey))
            ->first();
    }

    /**
     * Updates a gateway
     */
    public function updateGateway(int $id, array $postData): bool
    {
        if (!$this->find($id)) {
            return false;
        }

        $environment = ($postData['environment'] ?? '') == 'production' ? 'production' : 'sandbox';

        $data = [
            'public_key'      => $postData['public_key'] ?? '',
            'secret_key'      => $postData['secret_key'] ?? '',
            'webhook_secret'  => $postData['webhook_secret'] ?? '',
            'environment'     => $environment,
            'transaction_fee_rate' => numToDecimal($postData['transaction_fee_rate'] ?? 0),
            'status'          => (int)($postData['status'] ?? 0),
        ];

        return (bool)$this->update($id, $data);
    }

    /**
     * Retrieves the Gateway configuration securely from the database
     */
    public function getGatewayConfig(string $nameKey): object
    {
        $gateway = $this->where('name_key', $nameKey)->first();

        return (object)[
            'public_key'     => $gateway->public_key ?? '',
            'secret_key'     => $gateway->secret_key ?? '',
            'environment'    => ($gateway->environment ?? 'sandbox') == 'production' ? 'production' : 'sandbox',
            'webhook_secret' => $gateway->webhook_secret ?? '',
            'is_active'      => (int)($gateway->status ?? 0) === 1
        ];
    }
}