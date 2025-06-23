<?php

namespace MiniERP\Domain\Entities;

use MiniERP\Domain\Exceptions\InvalidPriceException;
use MiniERP\Domain\ValueObjects\ProductVariation;

class Product
{
    private ?int $id;
    private string $name;
    private float $price;
    /** @var ProductVariation[] */
    private array $variations;

    public function __construct(
        ?int $id,
        string $name,
        float $price,
        array $variations = []
    ) {
        $this->id = $id;
        $this->setName($name);
        $this->setPrice($price);
        $this->variations = $variations;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if (strlen($name) < 3) {
            throw new \InvalidArgumentException("Product name must be at least 3 characters");
        }
        $this->name = $name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        if ($price <= 0) {
            throw new InvalidPriceException("Price must be greater than zero");
        }
        $this->price = $price;
    }

    /** @return ProductVariation[] */
    public function getVariations(): array
    {
        return $this->variations;
    }

    public function addVariation(ProductVariation $variation): void
    {
        $this->variations[] = $variation;
    }

    public function removeVariation(ProductVariation $variationToRemove): void
    {
        $this->variations = array_filter(
            $this->variations,
            fn(ProductVariation $variation) => $variation->getSku() !== $variationToRemove->getSku()
        );
    }
}