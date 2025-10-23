<?php

namespace App\EventListener;

use App\Entity\OrderItem;
use App\Entity\Order;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\EntityManagerInterface;

class OrderItemListener
{
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if ($entity instanceof OrderItem) {
            // Recalculate item total when quantity or unitPrice changes
            if ($args->hasChangedField('quantity') || $args->hasChangedField('unitPrice')) {
                $this->recalculateItemTotal($entity);
            }
        }
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if ($entity instanceof OrderItem) {
            $this->recalculateOrderTotals($entity, $args->getObjectManager());
        }
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if ($entity instanceof OrderItem) {
            $this->recalculateItemTotal($entity);
        }
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if ($entity instanceof OrderItem) {
            $this->recalculateOrderTotals($entity, $args->getObjectManager());
        }
    }

    private function recalculateItemTotal(OrderItem $item): void
    {
        if ($item->getUnitPrice() && $item->getQuantity()) {
            $total = (float) $item->getUnitPrice() * $item->getQuantity();
            $item->setTotal(number_format($total, 2, '.', ''));
        }
    }

    private function recalculateOrderTotals(OrderItem $item, EntityManagerInterface $em): void
    {
        if ($item->getOrderRef()) {
            $order = $item->getOrderRef();
            $order->recalculateTotals();
            
            $em->persist($order);
            $em->flush();
            
            error_log("Order #{$order->getId()} totals recalculated via listener");
        }
    }
}
