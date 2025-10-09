<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Enum\DeliveryMode;
use App\Enum\OrderStatus;
use App\Enum\PaymentMode;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service pour gérer les commandes.
 */
class OrderService
{
    // TAX_RATE moved to RestaurantSettingsService

    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderRepository $orderRepository,
        private CartService $cartService,
        private RequestStack $requestStack,
        private RestaurantSettingsService $restaurantSettings
    ) {}

    /**
     * Créer une nouvelle commande à partir du panier
     */
    public function createOrder(array $orderData): Order
    {
        // Récupérer le panier
        $cart = $this->cartService->getCart();
        
        if (empty($cart['items'])) {
            throw new \InvalidArgumentException("Le panier est vide");
        }

        // Créer l'entité Order
        $order = new Order();
        $order->setNo($this->generateOrderNumber());
        $order->setStatus(OrderStatus::PENDING);
        $order->setCreatedAt(new \DateTimeImmutable());

        // Définir le mode de livraison
        $deliveryMode = isset($orderData['deliveryMode']) 
            ? DeliveryMode::from($orderData['deliveryMode'])
            : DeliveryMode::DELIVERY;
        $order->setDeliveryMode($deliveryMode);

        // Définir l'adresse de livraison si le mode est delivery
        if ($deliveryMode === DeliveryMode::DELIVERY) {
            if (empty($orderData['deliveryAddress'])) {
                throw new \InvalidArgumentException("L'adresse de livraison est requise");
            }
            $order->setDeliveryAddress($orderData['deliveryAddress']);
            $order->setDeliveryZip($orderData['deliveryZip'] ?? null);
            $order->setDeliveryInstructions($orderData['deliveryInstructions'] ?? null);
            $order->setDeliveryFee($orderData['deliveryFee'] ?? number_format($this->restaurantSettings->getDeliveryFee(), 2, '.', ''));
        } else {
            $order->setDeliveryFee('0.00');
        }

        // Définir le mode de paiement
        $paymentMode = isset($orderData['paymentMode']) 
            ? PaymentMode::from($orderData['paymentMode'])
            : PaymentMode::CARD;
        $order->setPaymentMode($paymentMode);

        // Définir les informations client
        $order->setClientFirstName($orderData['clientFirstName'] ?? null);
        $order->setClientLastName($orderData['clientLastName'] ?? null);
        $order->setClientPhone($orderData['clientPhone'] ?? null);
        $order->setClientEmail($orderData['clientEmail'] ?? null);
        
        // Générer le nom complet automatiquement si possible
        if ($order->getClientFirstName() && $order->getClientLastName()) {
            $order->setClientName($order->getClientFirstName() . ' ' . $order->getClientLastName());
        }

        // Calculer les montants
        // Цены в корзине уже включают налоги (TTC)
        $subtotalWithTax = $cart['total'];
        $taxRate = $this->restaurantSettings->getVatRate();
        $subtotalWithoutTax = $subtotalWithTax / (1 + $taxRate);
        $taxAmount = $subtotalWithTax - $subtotalWithoutTax;
        $deliveryFee = (float) $order->getDeliveryFee();
        $total = $subtotalWithTax + $deliveryFee;

        $order->setSubtotal(number_format($subtotalWithoutTax, 2, '.', ''));
        $order->setTaxAmount(number_format($taxAmount, 2, '.', ''));
        $order->setTotal(number_format($total, 2, '.', ''));

        // Ajouter les items de commande
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

        // Persister la commande
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        // Vider le panier après la création de la commande
        $this->cartService->clear();

        return $order;
    }

    /**
     * Récupérer une commande par ID
     */
    public function getOrder(int $orderId): ?Order
    {
        return $this->orderRepository->find($orderId);
    }

    /**
     * Générer un numéro de commande unique
     */
    private function generateOrderNumber(): string
    {
        $date = (new \DateTime())->format('Ymd');
        $random = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        return "ORD-{$date}-{$random}";
    }

    /**
     * Mettre à jour le statut d'une commande
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
}

