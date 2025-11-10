<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Cart Update Quantity Request Data Transfer Object
 */
class CartUpdateQuantityRequest
{
    #[Assert\NotBlank(message: 'La quantité est requise')]
    #[Assert\Type(type: 'integer', message: 'La quantité doit être un nombre')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'La quantité ne peut pas être négative')]
    public ?int $quantity = null;
}


