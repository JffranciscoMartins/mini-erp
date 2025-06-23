<?php

namespace MiniERP\Infrastructure\Repositories;

use MiniERP\Domain\Entities\Stock;
use MiniERP\Domain\Repositories\StockRepositoryInterface;
use PDO;

class MysqlStockRepository implements StockRepositoryInterface
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = \MiniERP\Infrastructure\Database\Database::getInstance();
    }

    public function save(Stock $stock): Stock
    {
        $stmt = $this->connection->prepare(
            "INSERT INTO stocks (product_id, variation, quantity) VALUES (:product_id, :variation, :quantity)"
        );

        $stmt->execute([
            ':product_id' => $stock->getProductId(),
            ':variation' => $stock->getVariation(),
            ':quantity' => $stock->getQuantity()
        ]);

        $id = (int) $this->connection->lastInsertId();

        return new Stock(
            id: $id,
            productId: $stock->getProductId(),
            variation: $stock->getVariation(),
            quantity: $stock->getQuantity()
        );
    }

    public function update(Stock $stock): bool
    {
        $stmt = $this->connection->prepare(
            "UPDATE stocks 
             SET quantity = :quantity 
             WHERE product_id = :product_id AND variation = :variation"
        );

        return $stmt->execute([
            ':quantity' => $stock->getQuantity(),
            ':product_id' => $stock->getProductId(),
            ':variation' => $stock->getVariation()
        ]);
    }

    public function findByProductId(int $productId): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM stocks WHERE product_id = :product_id");
        $stmt->execute([':product_id' => $productId]);

        $stocks = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stocks[] = new Stock(
                id: (int) $row['id'],
                productId: (int) $row['product_id'],
                variation: $row['variation'],
                quantity: (int) $row['quantity']
            );
        }

        return $stocks;
    }

    public function deleteByProductId(int $productId): bool
    {
        $stmt = $this->connection->prepare("DELETE FROM stocks WHERE product_id = :product_id");
        return $stmt->execute([':product_id' => $productId]);
    }
}
