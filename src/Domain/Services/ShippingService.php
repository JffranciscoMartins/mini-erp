<?php

namespace MiniERP\Domain\Services;

class ShippingService
{
    public function calculateShipping(float $subtotal): float
    {
        return match (true) {
            $subtotal > 200.00 => 0.0,
            $subtotal >= 52.00 && $subtotal <= 166.59 => 15.00,
            default => 20.00, 
        };
    }
}