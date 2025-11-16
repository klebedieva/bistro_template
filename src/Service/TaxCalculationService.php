<?php

namespace App\Service;

use App\Entity\Order;

class TaxCalculationService
{
    public function __construct(
        private RestaurantSettingsService $restaurantSettings
    ) {
    }

    /**
     * Recalculate full monetary totals for an order in one pass.
     * This method:
     * - Recalculates line items
     * - Splits TTC into HT/TVA
     * - Applies coupon OR manual discount (if provided)
     * - Persists formatted monetary values on the Order entity
     *
     * It is intentionally beginner-friendly and self-contained so callers
     * don't need to orchestrate multiple steps.
     */
    public function recalculateTotals(\App\Entity\Order $order, ?\App\Entity\Coupon $coupon, ?float $manualDiscount): void
    {
        // 1) Recalculate items and sum TTC
        $subtotalWithTax = 0.0;
        foreach ($order->getItems() as $item) {
            $item->recalculateTotal();
            $subtotalWithTax += (float) $item->getTotal();
        }

        // 2) HT/TVA from TTC
        $taxBreakdown = $this->calculateTaxFromTTC($subtotalWithTax);
        $order->setSubtotal($this->formatAmount($taxBreakdown['amountWithoutTax']));
        $order->setTaxAmount($this->formatAmount($taxBreakdown['taxAmount']));

        // 3) Delivery fee
        $deliveryFee = (float) ($order->getDeliveryFee() ?? 0);

        // 4) Reset discounts state to avoid accumulation on repeated calls
        $order->setCoupon(null);
        $order->setDiscountAmount('0.00');

        // 5) Apply either coupon discount or manual discount
        $orderAmountBeforeDiscount = $subtotalWithTax + $deliveryFee;
        $discountToApply = 0.0;

        if ($coupon !== null) {
            // Let coupon compute discount against current amount
            $discountToApply = max(0.0, (float) $coupon->calculateDiscount($orderAmountBeforeDiscount));
            $order->setCoupon($coupon);
        } elseif ($manualDiscount !== null) {
            // Clamp manual discount to [0, orderAmountBeforeDiscount]
            $discountToApply = max(0.0, min((float) $manualDiscount, $orderAmountBeforeDiscount));
        }

        $order->setDiscountAmount($this->formatAmount($discountToApply));

        // 6) Total
        $total = max($orderAmountBeforeDiscount - $discountToApply, 0.0);
        $order->setTotal($this->formatAmount($total));
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
     * Applies all monetary calculations (subtotal, taxes, delivery fee, discounts)
     * directly onto the provided order entity.
     *
     * Key steps:
     * 1. Recalculate each line item total to ensure products use the latest price/qty.
     * 2. Sum the cart amount including taxes (TTC) and break it down into HT/TVA.
     * 3. Re-apply coupon/discount amounts so the order always reflects the latest rules.
     * 4. Persist formatted monetary values (two decimals, stored as strings for DECIMAL columns).
     *
     * @param Order $order Order entity whose totals should be refreshed.
     */
    public function applyOrderTotals(Order $order): void
    {
        // Kept for backward compatibility with existing code paths.
        // New code should prefer recalculateTotals() which accepts coupon/discount input.
        $subtotalWithTax = 0.0;

        foreach ($order->getItems() as $item) {
            $item->recalculateTotal();
            $subtotalWithTax += (float) $item->getTotal();
        }

        $deliveryFee = (float) ($order->getDeliveryFee() ?? 0);

        $taxBreakdown = $this->calculateTaxFromTTC($subtotalWithTax);
        $order->setSubtotal($this->formatAmount($taxBreakdown['amountWithoutTax']));
        $order->setTaxAmount($this->formatAmount($taxBreakdown['taxAmount']));

        $discountAmount = (float) ($order->getDiscountAmount() ?? 0);
        if ($order->getCoupon() !== null) {
            $orderAmountBeforeDiscount = $subtotalWithTax + $deliveryFee;
            $calculatedDiscount = $order->getCoupon()->calculateDiscount($orderAmountBeforeDiscount);
            $order->setDiscountAmount($this->formatAmount($calculatedDiscount));
            $discountAmount = $calculatedDiscount;
        }

        $total = max($subtotalWithTax + $deliveryFee - $discountAmount, 0);
        $order->setTotal($this->formatAmount($total));
    }

    /**
     * Gets current tax rate
     */
    public function getTaxRate(): float
    {
        return $this->restaurantSettings->getVatRate();
    }

    /**
     * Helper to ensure all persisted monetary values keep a consistent format.
     */
    private function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
