<?php

namespace App\Models;

class CountryModel extends BaseModel
{
    protected $table = 'countries';
    protected $allowedFields = ['name', 'vat_rate'];

    /**
     * Retrieves countries
     */
    public function getCountries(bool $orderByVat = false): array
    {
        if ($orderByVat) {
            $this->orderBy('vat_rate', 'DESC');
        } else {
            $this->orderBy('name');
        }

        return $this->orderBy('id')->findAll();
    }

    /**
     * Update VAT rates for all countries in bulk
     */
    public function updateVatRates(array $vatRates): bool
    {
        if (empty($vatRates)) {
            return false;
        }

        $batchData = [];

        foreach ($vatRates as $countryId => $vatRate) {
            $finalRate = ($vatRate === '' || $vatRate === null) ? null : (float)$vatRate;

            $batchData[] = [
                'id'       => (int)$countryId,
                'vat_rate' => $finalRate
            ];
        }

        if (!empty($batchData)) {
            return $this->updateBatch($batchData, 'id') !== false;
        }

        return false;
    }
}