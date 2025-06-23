<?php

namespace MiniERP\Domain\Entities;

use MiniERP\Domain\ValueObjects\Cep;
use MiniERP\Domain\ValueObjects\OrderItem;

class Order
{
    private ?int $id;
    /** @var OrderItem[] */
    private array $items;
    private float $subtotal;
    private float $shipping;
    private float $total;
    private ?Cep $cep;
    private \DateTimeImmutable $createdAt;

    public function __construct(
        ?int $id,
        array $items,
        float $subtotal,
        float $shipping,
        float $total,
        ?Cep $cep = null,
        ?\DateTimeImmutable $createdAt = null
    ) {
        $this->id = $id;
        $this->items = $items;
        $this->subtotal = $subtotal;
        $this->shipping = $shipping;
        $this->total = $total;
        $this->cep = $cep;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
    }

    // Métodos de acesso...

    public function addItem(OrderItem $item): void
    {
        $this->items[] = $item;
        $this->recalculateTotals();
    }

    private function recalculateTotals(): void
    {
        $this->subtotal = array_reduce(
            $this->items,
            fn(float $carry, OrderItem $item) => $carry + $item->getTotal(),
            0
        );
        
        $this->calculateShipping();
        $this->total = $this->subtotal + $this->shipping;
    }

    private function calculateShipping(): void
    {
        if ($this->subtotal > 200) {
            $this->shipping = 0;
        } elseif ($this->subtotal >= 52 && $this->subtotal <= 166.59) {
            $this->shipping = 15;
        } else {
            $this->shipping = 20;
        }
    }
}