<?php

namespace MiniERP\Domain\Entities;

use MiniERP\Domain\Exceptions\NegativeStockException;

class Stock
{
    private ?int $id;
    private int $productId;
    private string $variation;
    private int $quantity;

    public function __construct(
        ?int $id,
        int $productId,
        string $variation,
        int $quantity
    ) {
        $this->id = $id;
        $this->productId = $productId;
        $this->variation = $variation;
        $this->setQuantity($quantity);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getVariation(): string
    {
        return $this->variation;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        if ($quantity < 0) {
            throw new NegativeStockException("Stock quantity cannot be negative");
        }
        $this->quantity = $quantity;
    }

    public function increase(int $amount): void
    {
        $this->setQuantity($this->quantity + $amount);
    }

    public function decrease(int $amount): void
    {
        $this->setQuantity($this->quantity - $amount);
    }
}