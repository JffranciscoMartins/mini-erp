<?php

namespace MiniERP\Domain\ValueObjects;

use MiniERP\Domain\Exceptions\InvalidVariationException;

class ProductVariation
{
    private string $sku;
    private string $name;
    private float $priceAdjustment;
    private string $description;

    public function __construct(
        string $sku,
        string $name,
        float $priceAdjustment = 0,
        string $description = ''
    ) {
        $this->setSku($sku);
        $this->setName($name);
        $this->priceAdjustment = $priceAdjustment;
        $this->description = $description;
    }

    private function setSku(string $sku): void
    {
        if (!preg_match('/^[A-Z0-9]{3,10}$/', $sku)) {
            throw new InvalidVariationException(
                "SKU must be 3-10 alphanumeric uppercase characters"
            );
        }
        $this->sku = $sku;
    }

    private function setName(string $name): void
    {
        if (strlen($name) < 2) {
            throw new InvalidVariationException(
                "Variation name must be at least 2 characters"
            );
        }
        $this->name = $name;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPriceAdjustment(): float
    {
        return $this->priceAdjustment;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}