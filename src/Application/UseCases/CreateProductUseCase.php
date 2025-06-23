<?php

namespace MiniERP\Application\UseCases;

use MiniERP\Domain\Entities\Product;
use MiniERP\Domain\Entities\Stock;
use MiniERP\Domain\Exceptions\InvalidPriceException;
use MiniERP\Domain\Exceptions\NegativeStockException;
use MiniERP\Domain\Repositories\ProductRepositoryInterface;
use MiniERP\Domain\Repositories\StockRepositoryInterface;
use MiniERP\Domain\ValueObjects\ProductVariation;

class CreateProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StockRepositoryInterface $stockRepository
    ) {}

    /**
     * @throws InvalidPriceException
     * @throws NegativeStockException
     */
    public function execute(
        string $name,
        float $price,
        array $variations = [],
        array $stockItems = []
    ): Product {
  
        $product = new Product(
            id: null,
            name: $name,
            price: $price,
            variations: array_map(
                fn(array $v) => new ProductVariation(
                    $v['sku'],
                    $v['name'],
                    $v['priceAdjustment'] ?? 0
                ),
                $variations
            )
        );

        $savedProduct = $this->productRepository->save($product);

        foreach ($stockItems as $item) {
            if ($item['quantity'] < 0) {
                throw new NegativeStockException("Stock quantity cannot be negative");
            }

            $stock = new Stock(
                id: null,
                productId: $savedProduct->getId(),
                variation: $item['variation'],
                quantity: $item['quantity']
            );

            $this->stockRepository->save($stock);
        }

        return $savedProduct;
    }
}