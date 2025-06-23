<?php

namespace MiniERP\Domain\Repositories;

use MiniERP\Domain\Entities\Stock;

interface StockRepositoryInterface
{
    public function save(Stock $stock): Stock;
    public function update(Stock $stock): bool;
    public function findByProductId(int $productId): array;
    public function deleteByProductId(int $productId): bool;
}
