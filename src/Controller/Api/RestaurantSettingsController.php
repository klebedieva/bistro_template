<?php

namespace App\Controller\Api;

use App\Service\RestaurantSettingsService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/restaurant', name: 'api_restaurant_')]
class RestaurantSettingsController extends AbstractController
{
    public function __construct(
        private RestaurantSettingsService $restaurantSettings
    ) {
    }

    #[Route('/settings', name: 'settings', methods: ['GET'])]
    #[OA\Get(
        path: '/api/restaurant/settings',
        summary: 'Get restaurant settings',
        description: 'Get all restaurant configuration settings including delivery fees, tax rates, etc.',
        tags: ['Restaurant Settings']
    )]
    #[OA\Response(
        response: 200,
        description: 'Restaurant settings retrieved successfully',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'settings', type: 'object', properties: [
                    new OA\Property(property: 'delivery', type: 'object', properties: [
                        new OA\Property(property: 'fee', type: 'number', format: 'float', example: 5.0),
                        new OA\Property(property: 'freeDeliveryThreshold', type: 'number', format: 'float', example: 50.0),
                        new OA\Property(property: 'radiusKm', type: 'integer', example: 5),
                    ]),
                    new OA\Property(property: 'tax', type: 'object', properties: [
                        new OA\Property(property: 'vatRate', type: 'number', format: 'float', example: 0.1),
                    ]),
                    new OA\Property(property: 'contact', type: 'object', properties: [
                        new OA\Property(property: 'phone', type: 'string', example: '+33 4 91 XX XX XX'),
                        new OA\Property(property: 'email', type: 'string', example: 'contact@letroisquarts.fr'),
                        new OA\Property(property: 'address', type: 'string', example: 'Marseille, France'),
                    ]),
                    new OA\Property(property: 'businessHours', type: 'object', properties: [
                        new OA\Property(property: 'daily', type: 'object', properties: [
                            new OA\Property(property: 'start', type: 'string', example: '07:00'),
                            new OA\Property(property: 'end', type: 'string', example: '23:00'),
                        ]),
                    ]),
                ]),
            ]
        )
    )]
    public function getSettings(): JsonResponse
    {
        $settings = [
            'delivery' => [
                'fee' => $this->restaurantSettings->getDeliveryFee(),
                'freeDeliveryThreshold' => $this->restaurantSettings->getFreeDeliveryThreshold(),
                'radiusKm' => $this->restaurantSettings->getDeliveryRadius(),
            ],
            'tax' => [
                'vatRate' => $this->restaurantSettings->getVatRate(),
            ],
            'contact' => [
                'phone' => $this->restaurantSettings->getContactPhone(),
                'email' => $this->restaurantSettings->getContactEmail(),
                'address' => $this->restaurantSettings->getContactAddress(),
            ],
            'businessHours' => $this->restaurantSettings->getBusinessHours(),
        ];

        return $this->json([
            'success' => true,
            'settings' => $settings,
        ]);
    }
}
