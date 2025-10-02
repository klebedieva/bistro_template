<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CartResponse',
    description: 'Cart response representation',
    type: 'object'
)]
class CartResponseDTO
{
    public function __construct(
        #[OA\Property(property: 'items', type: 'array', items: new OA\Items(type: 'object'), description: 'Cart items')]
        public array $items,

        #[OA\Property(property: 'total', type: 'number', format: 'float', example: 29.0, description: 'Total price')]
        public float $total,

        #[OA\Property(property: 'itemCount', type: 'integer', example: 2, description: 'Total number of items')]
        public int $itemCount
    ) {}

    public function toArray(): array
    {
        return [
            'items' => array_map(fn($item) => $item instanceof CartItemDTO ? $item->toArray() : $item, $this->items),
            'total' => $this->total,
            'itemCount' => $this->itemCount
        ];
    }
}

