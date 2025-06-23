<?php

namespace MiniERP\Infrastructure\Repositories;

use MiniERP\Domain\Entities\Product;
use MiniERP\Domain\Repositories\ProductRepositoryInterface;
use MiniERP\Domain\ValueObjects\ProductVariation;
use PDO;

class MysqlProductRepository implements ProductRepositoryInterface
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = \MiniERP\Infrastructure\Database\Database::getInstance();
    }

    public function save(Product $product): Product
    {
        $stmt = $this->connection->prepare(
            "INSERT INTO products (name, price) VALUES (:name, :price)"
        );
        $stmt->execute([
            ':name' => $product->getName(),
            ':price' => $product->getPrice(),
        ]);

        $productId = (int) $this->connection->lastInsertId();
        $this->saveVariations($productId, $product->getVariations());

        return new Product(
            id: $productId,
            name: $product->getName(),
            price: $product->getPrice(),
            variations: $product->getVariations()
        );
    }

    public function update(Product $product): bool
    {
        $stmt = $this->connection->prepare(
            "UPDATE products SET name = :name, price = :price WHERE id = :id"
        );
        $result = $stmt->execute([
            ':name' => $product->getName(),
            ':price' => $product->getPrice(),
            ':id' => $product->getId()
        ]);

        // Simples estratégia: remove e reinsere variações
        $this->connection->prepare("DELETE FROM product_variations WHERE product_id = :id")
            ->execute([':id' => $product->getId()]);

        $this->saveVariations($product->getId(), $product->getVariations());

        return $result;
    }

    public function findAll(): array
    {
        $stmt = $this->connection->query("SELECT * FROM products");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $products = [];
        foreach ($rows as $row) {
            $products[] = $this->hydrateProduct($row);
        }

        return $products;
    }

    public function findById(int $id): ?Product
    {
        $stmt = $this->connection->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return $this->hydrateProduct($row);
    }

    public function delete(int $id): bool
    {
        $this->connection->prepare("DELETE FROM product_variations WHERE product_id = :id")
            ->execute([':id' => $id]);

        $stmt = $this->connection->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * @param int $productId
     * @param ProductVariation[] $variations
     */
    private function saveVariations(int $productId, array $variations): void
    {
        if (empty($variations)) {
            return;
        }

        $stmt = $this->connection->prepare(
            "INSERT INTO product_variations (product_id, sku, name, price_adjustment) 
             VALUES (:product_id, :sku, :name, :price_adjustment)"
        );

        foreach ($variations as $variation) {
            $stmt->execute([
                ':product_id' => $productId,
                ':sku' => $variation->getSku(),
                ':name' => $variation->getName(),
                ':price_adjustment' => $variation->getPriceAdjustment(),
            ]);
        }
    }

    private function hydrateProduct(array $row): Product
    {
        $variations = [];

        $stmt = $this->connection->prepare("SELECT * FROM product_variations WHERE product_id = :id");
        $stmt->execute([':id' => $row['id']]);
        $variationRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($variationRows as $v) {
            $variations[] = new ProductVariation(
                sku: $v['sku'],
                name: $v['name'],
                priceAdjustment: $v['price_adjustment']
            );
        }

        return new Product(
            id: $row['id'],
            name: $row['name'],
            price: $row['price'],
            variations: $variations
        );
    }
}
