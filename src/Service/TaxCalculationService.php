<?php

namespace App\Service;

class TaxCalculationService
{
    public function __construct(
        private RestaurantSettingsService $restaurantSettings
    ) {
    }

    /**
     * Calculates taxes for amount including taxes (TTC)
     */
    public function calculateTaxFromTTC(float $amountWithTax): array
    {
        $taxRate = $this->restaurantSettings->getVatRate();
        $amountWithoutTax = $amountWithTax / (1 + $taxRate);
        $taxAmount = $amountWithTax - $amountWithoutTax;

        return [
            'amountWithoutTax' => round($amountWithoutTax, 2),
            'taxAmount' => round($taxAmount, 2),
            'amountWithTax' => round($amountWithTax, 2),
            'taxRate' => $taxRate,
        ];
    }

    /**
     * Calculates taxes for amount without taxes (HT)
     */
    public function calculateTaxFromHT(float $amountWithoutTax): array
    {
        $taxRate = $this->restaurantSettings->getVatRate();
        $taxAmount = $amountWithoutTax * $taxRate;
        $amountWithTax = $amountWithoutTax + $taxAmount;

        return [
            'amountWithoutTax' => round($amountWithoutTax, 2),
            'taxAmount' => round($taxAmount, 2),
            'amountWithTax' => round($amountWithTax, 2),
            'taxRate' => $taxRate,
        ];
    }

    /**
     * Gets current tax rate
     */
    public function getTaxRate(): float
    {
        return $this->restaurantSettings->getVatRate();
    }
}
