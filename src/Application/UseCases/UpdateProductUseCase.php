<?php

namespace MiniERP\Application\UseCases;

use MiniERP\Domain\Entities\Stock;
use MiniERP\Domain\Exceptions\NegativeStockException;
use MiniERP\Domain\Repositories\ProductRepositoryInterface;
use MiniERP\Domain\Repositories\StockRepositoryInterface;

class UpdateProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StockRepositoryInterface $stockRepository
    ) {}

    /**
     * @param int $productId
     * @param string $name
     * @param float $price
     * @param array $stockItems Ex: [['variation' => '123', 'quantity' => 5], ...]
     *
     * @return bool
     * 
     * @throws \DomainException
     */
    public function execute(int $productId, string $name, float $price, array $stockItems): bool
    {
        $product = $this->productRepository->findById($productId);

        if (!$product) {
            throw new \RuntimeException("Product with ID {$productId} not found.");
        }

        $product->setName($name);
        $product->setPrice($price);

        $productUpdated = $this->productRepository->update($product);

        foreach ($stockItems as $item) {
            $variation = $item['variation'] ?? null;
            $quantity = $item['quantity'] ?? null;

            if ($variation === null || !is_string($variation)) {
                throw new \InvalidArgumentException("Invalid variation identifier");
            }

            if (!is_numeric($quantity) || $quantity < 0) {
                throw new NegativeStockException("Stock quantity cannot be negative");
            }

            $stock = new Stock(
                id: null, 
                productId: $productId,
                variation: $variation,
                quantity: (int) $quantity
            );

            $this->stockRepository->update($stock);
        }

        return $productUpdated;
    }
}
