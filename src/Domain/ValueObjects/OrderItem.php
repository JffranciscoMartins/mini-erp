<?php

namespace MiniERP\Domain\ValueObjects;

use MiniERP\Domain\Entities\Product;

class OrderItem
{
    private Product $product;
    private string $variation;
    private int $quantity;
    private float $unitPrice;

    public function __construct(
        Product $product,
        string $variation,
        int $quantity,
        float $unitPrice
    ) {
        $this->product = $product;
        $this->variation = $variation;
        $this->setQuantity($quantity);
        $this->setUnitPrice($unitPrice);
    }

    private function setQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException(
                "Quantity must be greater than zero"
            );
        }
        $this->quantity = $quantity;
    }

    private function setUnitPrice(float $unitPrice): void
    {
        if ($unitPrice <= 0) {
            throw new \InvalidArgumentException(
                "Unit price must be greater than zero"
            );
        }
        $this->unitPrice = $unitPrice;
    }

    public function getTotal(): float
    {
        return $this->unitPrice * $this->quantity;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getVariation(): string
    {
        return $this->variation;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }
}