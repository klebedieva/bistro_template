<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Coupon;
use App\Enum\DeliveryMode;
use App\Enum\OrderStatus;
use App\Enum\PaymentMode;
use App\Repository\OrderRepository;
use App\Repository\CouponRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service to manage orders.
 */
class OrderService
{
    // TAX_RATE moved to RestaurantSettingsService

    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderRepository $orderRepository,
        private CartService $cartService,
        private RequestStack $requestStack,
        private RestaurantSettingsService $restaurantSettings,
        private AddressValidationService $addressValidationService,
        private CouponRepository $couponRepository
    ) {}

    /**
     * Create a new order from cart
     */
    public function createOrder(array $orderData): Order
    {
        // Get the cart
        $cart = $this->cartService->getCart();
        
        if (empty($cart['items'])) {
            throw new \InvalidArgumentException("Le panier est vide");
        }

        // Create Order entity
        $order = new Order();
        $order->setNo($this->generateOrderNumber());
        $order->setStatus(OrderStatus::PENDING);
        $order->setCreatedAt(new \DateTimeImmutable());

        // Set delivery mode
        $deliveryMode = isset($orderData['deliveryMode']) 
            ? DeliveryMode::from($orderData['deliveryMode'])
            : DeliveryMode::DELIVERY;
        $order->setDeliveryMode($deliveryMode);

        // Set delivery address if mode is delivery
        if ($deliveryMode === DeliveryMode::DELIVERY) {
            if (empty($orderData['deliveryAddress'])) {
                throw new \InvalidArgumentException("L'adresse de livraison est requise");
            }
            
            // Validate full address for delivery
            $deliveryZip = $orderData['deliveryZip'] ?? null;
            $addressValidation = $this->addressValidationService->validateAddressForDelivery($orderData['deliveryAddress'], $deliveryZip);
            if (!$addressValidation['valid']) {
                throw new \InvalidArgumentException($addressValidation['error'] ?? 'Livraison non disponible pour cette adresse');
            }
            
            $order->setDeliveryAddress($orderData['deliveryAddress']);
            $order->setDeliveryZip($deliveryZip);
            $order->setDeliveryInstructions($orderData['deliveryInstructions'] ?? null);
            $order->setDeliveryFee($orderData['deliveryFee'] ?? number_format($this->restaurantSettings->getDeliveryFee(), 2, '.', ''));
        } else {
            $order->setDeliveryFee('0.00');
        }

        // Set payment mode
        $paymentMode = isset($orderData['paymentMode']) 
            ? PaymentMode::from($orderData['paymentMode'])
            : PaymentMode::CARD;
        $order->setPaymentMode($paymentMode);

        // Set client information
        $order->setClientFirstName($orderData['clientFirstName'] ?? null);
        $order->setClientLastName($orderData['clientLastName'] ?? null);
        
        // French phone number validation
        $clientPhone = $orderData['clientPhone'] ?? null;
        if ($clientPhone && !$this->validateFrenchPhoneNumber($clientPhone)) {
            throw new \InvalidArgumentException("Numéro de téléphone invalide");
        }
        $order->setClientPhone($clientPhone);
        
        $order->setClientEmail($orderData['clientEmail'] ?? null);
        
        // Generate full name automatically if possible
        if ($order->getClientFirstName() && $order->getClientLastName()) {
            $order->setClientName($order->getClientFirstName() . ' ' . $order->getClientLastName());
        }

        // Calculate amounts
        // Cart prices already include taxes (TTC)
        $subtotalWithTax = $cart['total'];
        $taxRate = $this->restaurantSettings->getVatRate();
        $subtotalWithoutTax = $subtotalWithTax / (1 + $taxRate);
        $taxAmount = $subtotalWithTax - $subtotalWithoutTax;
        $deliveryFee = (float) $order->getDeliveryFee();
        $total = $subtotalWithTax + $deliveryFee;

        // Handle coupon if provided
        $discount = 0;
        if (isset($orderData['couponId'])) {
            $coupon = $this->couponRepository->find($orderData['couponId']);
            
            if ($coupon && $coupon->canBeAppliedToAmount($total)) {
                $discount = $coupon->calculateDiscount($total);
                $order->setCoupon($coupon);
                $order->setDiscountAmount(number_format($discount, 2, '.', ''));
                $total = $total - $discount;
            } elseif (isset($orderData['discountAmount'])) {
                // Fallback to discount amount if coupon is not valid
                $discount = (float) $orderData['discountAmount'];
                $order->setDiscountAmount(number_format($discount, 2, '.', ''));
                $total = $total - $discount;
            }
        }

        $order->setSubtotal(number_format($subtotalWithoutTax, 2, '.', ''));
        $order->setTaxAmount(number_format($taxAmount, 2, '.', ''));
        $order->setTotal(number_format($total, 2, '.', ''));

        // Add order items
        foreach ($cart['items'] as $cartItem) {
            $orderItem = new OrderItem();
            $orderItem->setProductId($cartItem['id']);
            $orderItem->setProductName($cartItem['name']);
            $orderItem->setUnitPrice((string) $cartItem['price']);
            $orderItem->setQuantity($cartItem['quantity']);
            $orderItem->setTotal((string) ($cartItem['price'] * $cartItem['quantity']));
            $orderItem->setOrderRef($order);
            
            $order->addItem($orderItem);
        }

        // Persist the order
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        // Clear cart after order creation
        $this->cartService->clear();

        return $order;
    }

    /**
     * Get order by ID
     */
    public function getOrder(int $orderId): ?Order
    {
        return $this->orderRepository->find($orderId);
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber(): string
    {
        $date = (new \DateTime())->format('Ymd');
        $random = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        return "ORD-{$date}-{$random}";
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(int $orderId, string $status): Order
    {
        $order = $this->getOrder($orderId);
        
        if (!$order) {
            throw new \InvalidArgumentException("Commande introuvable: $orderId");
        }

        $orderStatus = OrderStatus::from($status);
        $order->setStatus($orderStatus);
        
        $this->entityManager->flush();
        
        return $order;
    }

    /**
     * Validate French phone number
     */
    private function validateFrenchPhoneNumber(string $phone): bool
    {
        if (empty($phone)) {
            return false;
        }

        // Clean number (remove spaces, dashes, dots)
        $cleanPhone = preg_replace('/[\s\-\.]/', '', $phone);

        // Check length and general format first
        // National format: 0X XXXX XXXX (10 digits total, starts with 0)
        // International format: +33 X XX XX XX XX (12 characters, starts with +33)

        if (strlen($cleanPhone) === 10 && str_starts_with($cleanPhone, '0')) {
            // French national format: 0X XXXX XXXX
            if (!preg_match('/^0[1-9]\d{8}$/', $cleanPhone)) {
                return false;
            }
            
            // Check first digits for mobiles (06, 07) and landlines (01-05)
            $firstTwoDigits = substr($cleanPhone, 0, 2);
            $validPrefixes = ['06', '07', '01', '02', '03', '04', '05'];
            return in_array($firstTwoDigits, $validPrefixes, true);
            
        } elseif (strlen($cleanPhone) === 12 && str_starts_with($cleanPhone, '+33')) {
            // International format: +33 X XX XX XX XX
            if (!preg_match('/^\+33[1-9]\d{8}$/', $cleanPhone)) {
                return false;
            }
            
            // Extract number without country code (+33)
            $withoutCountryCode = substr($cleanPhone, 3); // Remove '+33'
            
            // Check first digits for mobiles (06, 07) and landlines (01-05)
            $firstTwoDigits = substr($withoutCountryCode, 0, 2);
            $validPrefixes = ['06', '07', '01', '02', '03', '04', '05'];
            return in_array($firstTwoDigits, $validPrefixes, true);
        }

        // If neither 10 digits with 0, nor 12 characters with +33, then invalid
        return false;
    }
}

