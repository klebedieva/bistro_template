<?php

namespace App\Controller\Api;

use App\Entity\GalleryImage;
use App\Repository\GalleryImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/gallery', name: 'api_gallery_')]
class GalleryController extends AbstractController
{
    public function __construct(
        private GalleryImageRepository $galleryRepository
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/gallery',
        summary: 'Get gallery images',
        description: 'Retrieve a list of active gallery images',
        tags: ['Gallery'],
        parameters: [
            new OA\Parameter(
                name: 'limit',
                description: 'Maximum number of images to return',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 50)
            ),
            new OA\Parameter(
                name: 'category',
                description: 'Filter by category',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['terrasse', 'interieur', 'plats', 'ambiance'])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Gallery images retrieved successfully',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'title', type: 'string', example: 'Terrasse conviviale'),
                                new OA\Property(property: 'description', type: 'string', example: 'Un espace agréable pour vos repas en extérieur'),
                                new OA\Property(property: 'imageUrl', type: 'string', example: '/assets/img/terrasse_2.jpg'),
                                new OA\Property(property: 'category', type: 'string', example: 'terrasse'),
                                new OA\Property(property: 'displayOrder', type: 'integer', example: 1),
                                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00+00:00')
                            ]
                        )),
                        new OA\Property(property: 'total', type: 'integer', example: 10)
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Invalid parameters')
                    ]
                )
            )
        ]
    )]
    public function list(Request $request): JsonResponse
    {
        try {
            $limit = (int) $request->query->get('limit', 50);
            $category = $request->query->get('category');

            // Validate limit
            if ($limit < 1 || $limit > 100) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Limit must be between 1 and 100'
                ], 400);
            }

            // Validate category if provided
            if ($category && !in_array($category, ['terrasse', 'interieur', 'plats', 'ambiance'])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Invalid category. Must be one of: terrasse, interieur, plats, ambiance'
                ], 400);
            }

            // Get images from repository
            if ($category) {
                $images = $this->galleryRepository->findByCategory($category);
            } else {
                $images = $this->galleryRepository->findAllActive();
            }

            // Limit results
            $images = array_slice($images, 0, $limit);

            // Format response data
            $req = $request; // capture for closure
            $data = array_map(function (GalleryImage $image) use ($req) {
                return [
                    'id' => $image->getId(),
                    'title' => $image->getTitle(),
                    'description' => $image->getDescription(),
                    'imageUrl' => $req->getSchemeAndHttpHost() . '/assets/img/' . $image->getImagePath(),
                    'category' => $image->getCategory(),
                    'displayOrder' => $image->getDisplayOrder(),
                    'createdAt' => $image->getCreatedAt()->format('c')
                ];
            }, $images);

            return new JsonResponse([
                'success' => true,
                'data' => $data,
                'total' => count($data)
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'An error occurred while retrieving gallery images'
            ], 500);
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(
        path: '/api/gallery/{id}',
        summary: 'Get gallery image by ID',
        description: 'Retrieve a specific gallery image by its ID',
        tags: ['Gallery'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Gallery image ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Gallery image retrieved successfully',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'title', type: 'string', example: 'Terrasse conviviale'),
                            new OA\Property(property: 'description', type: 'string', example: 'Un espace agréable pour vos repas en extérieur'),
                            new OA\Property(property: 'imageUrl', type: 'string', example: '/assets/img/terrasse_2.jpg'),
                            new OA\Property(property: 'category', type: 'string', example: 'terrasse'),
                            new OA\Property(property: 'displayOrder', type: 'integer', example: 1),
                            new OA\Property(property: 'isActive', type: 'boolean', example: true),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00+00:00'),
                            new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00+00:00')
                        ])
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Gallery image not found',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Gallery image not found')
                    ]
                )
            )
        ]
    )]
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $image = $this->galleryRepository->find($id);

            if (!$image || !$image->isIsActive()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Gallery image not found'
                ], 404);
            }

            $data = [
                'id' => $image->getId(),
                'title' => $image->getTitle(),
                'description' => $image->getDescription(),
                'imageUrl' => $request->getSchemeAndHttpHost() . '/assets/img/' . $image->getImagePath(),
                'category' => $image->getCategory(),
                'displayOrder' => $image->getDisplayOrder(),
                'isActive' => $image->isIsActive(),
                'createdAt' => $image->getCreatedAt()->format('c'),
                'updatedAt' => $image->getUpdatedAt() ? $image->getUpdatedAt()->format('c') : null
            ];

            return new JsonResponse([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'An error occurred while retrieving the gallery image'
            ], 500);
        }
    }
}
