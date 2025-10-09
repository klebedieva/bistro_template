<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class RestaurantSettingsService
{
    public function __construct(
        private ParameterBagInterface $parameterBag
    ) {
    }

    public function getDeliveryFee(): float
    {
        $restaurant = $this->parameterBag->get('restaurant');
        return (float) $restaurant['delivery']['fee'];
    }

    public function getFreeDeliveryThreshold(): float
    {
        $restaurant = $this->parameterBag->get('restaurant');
        return (float) $restaurant['delivery']['free_delivery_threshold'];
    }

    public function getDeliveryRadius(): int
    {
        $restaurant = $this->parameterBag->get('restaurant');
        return (int) $restaurant['delivery']['radius_km'];
    }

    public function getVatRate(): float
    {
        $restaurant = $this->parameterBag->get('restaurant');
        return (float) $restaurant['tax']['vat_rate'];
    }

    public function getContactPhone(): string
    {
        $restaurant = $this->parameterBag->get('restaurant');
        return $restaurant['contact']['phone'];
    }

    public function getContactEmail(): string
    {
        $restaurant = $this->parameterBag->get('restaurant');
        return $restaurant['contact']['email'];
    }

    public function getContactAddress(): string
    {
        $restaurant = $this->parameterBag->get('restaurant');
        return $restaurant['contact']['address'];
    }

    public function getBusinessHours(): array
    {
        $restaurant = $this->parameterBag->get('restaurant');
        return [
            'daily' => [
                'start' => $restaurant['business_hours']['daily']['start'],
                'end' => $restaurant['business_hours']['daily']['end'],
            ],
        ];
    }
}
