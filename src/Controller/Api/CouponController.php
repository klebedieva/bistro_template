<?php

namespace App\Controller\Api;

use App\Entity\Coupon;
use App\Repository\CouponRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/coupon', name: 'api_coupon_')]
class CouponController extends AbstractController
{
    public function __construct(
        private CouponRepository $couponRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Validate and apply a coupon code
     * 
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/validate', name: 'validate', methods: ['POST'])]
    public function validate(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['code'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Code promo requis'
                ], 400);
            }

            if (!isset($data['orderAmount'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Montant de commande requis'
                ], 400);
            }

            $code = strtoupper(trim($data['code']));
            $orderAmount = (float) $data['orderAmount'];

            // Find coupon by code
            $coupon = $this->couponRepository->findOneBy(['code' => $code]);

            if (!$coupon) {
                return $this->json([
                    'success' => false,
                    'message' => 'Code promo invalide'
                ], 404);
            }

            // Check if coupon is active
            if (!$coupon->isActive()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Ce code promo n\'est plus actif'
                ], 400);
            }

            // Check if coupon is valid (dates, usage limit)
            if (!$coupon->canBeUsed()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Ce code promo n\'est plus disponible'
                ], 400);
            }

            // Check minimum order amount
            if (!$coupon->canBeAppliedToAmount($orderAmount)) {
                $minAmount = $coupon->getMinOrderAmount();
                return $this->json([
                    'success' => false,
                    'message' => sprintf(
                        'Montant minimum de commande non atteint (minimum: %.2f€)',
                        (float) $minAmount
                    )
                ], 400);
            }

            // Calculate discount
            $discountAmount = $coupon->calculateDiscount($orderAmount);

            return $this->json([
                'success' => true,
                'message' => 'Code promo appliqué avec succès',
                'data' => [
                    'couponId' => $coupon->getId(),
                    'code' => $coupon->getCode(),
                    'discountType' => $coupon->getDiscountType(),
                    'discountValue' => $coupon->getDiscountValue(),
                    'discountAmount' => number_format($discountAmount, 2, '.', ''),
                    'newTotal' => number_format($orderAmount - $discountAmount, 2, '.', '')
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la validation du code promo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply coupon to order (when order is placed)
     * This method increments the usage count
     * 
     * @param int $couponId
     * @return JsonResponse
     */
    #[Route('/apply/{couponId}', name: 'apply', methods: ['POST'])]
    public function apply(int $couponId): JsonResponse
    {
        try {
            $coupon = $this->couponRepository->find($couponId);

            if (!$coupon) {
                return $this->json([
                    'success' => false,
                    'message' => 'Code promo non trouvé'
                ], 404);
            }

            if (!$coupon->canBeUsed()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Ce code promo n\'est plus disponible'
                ], 400);
            }

            // Increment usage count
            $coupon->incrementUsage();
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Code promo appliqué'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de l\'application du code promo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active coupons (admin only)
     * 
     * @return JsonResponse
     */
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        try {
            $coupons = $this->couponRepository->findBy(
                ['isActive' => true],
                ['createdAt' => 'DESC']
            );

            $data = array_map(function (Coupon $coupon) {
                return [
                    'id' => $coupon->getId(),
                    'code' => $coupon->getCode(),
                    'description' => $coupon->getDescription(),
                    'discountType' => $coupon->getDiscountType(),
                    'discountValue' => $coupon->getDiscountValue(),
                    'minOrderAmount' => $coupon->getMinOrderAmount(),
                    'maxDiscount' => $coupon->getMaxDiscount(),
                    'usageLimit' => $coupon->getUsageLimit(),
                    'usageCount' => $coupon->getUsageCount(),
                    'validFrom' => $coupon->getValidFrom()?->format('Y-m-d H:i:s'),
                    'validUntil' => $coupon->getValidUntil()?->format('Y-m-d H:i:s'),
                    'isValid' => $coupon->isValid(),
                    'canBeUsed' => $coupon->canBeUsed()
                ];
            }, $coupons);

            return $this->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des codes promo: ' . $e->getMessage()
            ], 500);
        }
    }
}

