<?php

namespace App\DataFixtures;

use App\Entity\Coupon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CouponFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create test coupons
        
        // 1. Percentage discount (10% off)
        $coupon1 = new Coupon();
        $coupon1->setCode('PROMO10');
        $coupon1->setDescription('10% de réduction sur votre commande');
        $coupon1->setDiscountType(Coupon::TYPE_PERCENTAGE);
        $coupon1->setDiscountValue('10');
        $coupon1->setMinOrderAmount('20');
        $coupon1->setMaxDiscount(null);
        $coupon1->setUsageLimit(100);
        $coupon1->setIsActive(true);
        $coupon1->setValidFrom(new \DateTime('-1 day'));
        $coupon1->setValidUntil(new \DateTime('+30 days'));
        $manager->persist($coupon1);

        // 2. Fixed discount (5€ off)
        $coupon2 = new Coupon();
        $coupon2->setCode('WELCOME5');
        $coupon2->setDescription('5€ de réduction pour les nouveaux clients');
        $coupon2->setDiscountType(Coupon::TYPE_FIXED);
        $coupon2->setDiscountValue('5.00');
        $coupon2->setMinOrderAmount('15');
        $coupon2->setMaxDiscount(null);
        $coupon2->setUsageLimit(50);
        $coupon2->setIsActive(true);
        $coupon2->setValidFrom(new \DateTime('-1 day'));
        $coupon2->setValidUntil(new \DateTime('+60 days'));
        $manager->persist($coupon2);

        // 3. Percentage discount with max limit (20% off, max 10€)
        $coupon3 = new Coupon();
        $coupon3->setCode('SUMMER20');
        $coupon3->setDescription('20% de réduction (max 10€) - Promotion été');
        $coupon3->setDiscountType(Coupon::TYPE_PERCENTAGE);
        $coupon3->setDiscountValue('20');
        $coupon3->setMinOrderAmount('25');
        $coupon3->setMaxDiscount('10');
        $coupon3->setUsageLimit(200);
        $coupon3->setIsActive(true);
        $coupon3->setValidFrom(new \DateTime('-1 day'));
        $coupon3->setValidUntil(new \DateTime('+90 days'));
        $manager->persist($coupon3);

        // 4. Special offer - no minimum (3€ off)
        $coupon4 = new Coupon();
        $coupon4->setCode('SPECIAL3');
        $coupon4->setDescription('3€ de réduction sans minimum');
        $coupon4->setDiscountType(Coupon::TYPE_FIXED);
        $coupon4->setDiscountValue('3.00');
        $coupon4->setMinOrderAmount(null);
        $coupon4->setMaxDiscount(null);
        $coupon4->setUsageLimit(30);
        $coupon4->setIsActive(true);
        $coupon4->setValidFrom(new \DateTime('-1 day'));
        $coupon4->setValidUntil(new \DateTime('+15 days'));
        $manager->persist($coupon4);

        // 5. VIP discount (15% off, minimum 50€)
        $coupon5 = new Coupon();
        $coupon5->setCode('VIP15');
        $coupon5->setDescription('15% de réduction pour les commandes VIP (min 50€)');
        $coupon5->setDiscountType(Coupon::TYPE_PERCENTAGE);
        $coupon5->setDiscountValue('15');
        $coupon5->setMinOrderAmount('50');
        $coupon5->setMaxDiscount('20');
        $coupon5->setUsageLimit(null); // Unlimited
        $coupon5->setIsActive(true);
        $coupon5->setValidFrom(new \DateTime('-1 day'));
        $coupon5->setValidUntil(null); // No expiration
        $manager->persist($coupon5);

        // 6. Expired coupon (for testing)
        $coupon6 = new Coupon();
        $coupon6->setCode('EXPIRED');
        $coupon6->setDescription('Code promo expiré (pour test)');
        $coupon6->setDiscountType(Coupon::TYPE_FIXED);
        $coupon6->setDiscountValue('10.00');
        $coupon6->setMinOrderAmount(null);
        $coupon6->setMaxDiscount(null);
        $coupon6->setUsageLimit(100);
        $coupon6->setIsActive(true);
        $coupon6->setValidFrom(new \DateTime('-30 days'));
        $coupon6->setValidUntil(new \DateTime('-1 day'));
        $manager->persist($coupon6);

        // 7. Inactive coupon (for testing)
        $coupon7 = new Coupon();
        $coupon7->setCode('INACTIVE');
        $coupon7->setDescription('Code promo inactif (pour test)');
        $coupon7->setDiscountType(Coupon::TYPE_PERCENTAGE);
        $coupon7->setDiscountValue('25');
        $coupon7->setMinOrderAmount(null);
        $coupon7->setMaxDiscount(null);
        $coupon7->setUsageLimit(null);
        $coupon7->setIsActive(false);
        $coupon7->setValidFrom(new \DateTime('-1 day'));
        $coupon7->setValidUntil(new \DateTime('+30 days'));
        $manager->persist($coupon7);

        $manager->flush();
    }
}

