<?php

namespace MiniERP\Domain\Entities;

use MiniERP\Domain\Exceptions\CouponExpiredException;
use MiniERP\Domain\Exceptions\CouponMinValueException;

class Coupon
{
    private ?int $id;
    private string $code;
    private float $discount;
    private float $minValue;
    private \DateTimeImmutable $expiresAt;

    public function __construct(
        ?int $id,
        string $code,
        float $discount,
        float $minValue = 0,
        ?\DateTimeImmutable $expiresAt = null
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->discount = $discount;
        $this->minValue = $minValue;
        $this->expiresAt = $expiresAt ?? (new \DateTimeImmutable())->modify('+30 days');
    }

    public function isValidForOrder(float $orderSubtotal): bool
    {
        if ($this->isExpired()) {
            throw new CouponExpiredException("Coupon has expired");
        }

        if ($orderSubtotal < $this->minValue) {
            throw new CouponMinValueException(
                sprintf("Order subtotal must be at least %.2f to use this coupon", $this->minValue)
            );
        }

        return true;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new \DateTimeImmutable();
    }

    public function applyDiscount(float $amount): float
    {
        return $amount - ($amount * ($this->discount / 100));
    }
}