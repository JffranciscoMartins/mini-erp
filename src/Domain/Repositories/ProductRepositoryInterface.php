<?php

namespace MiniERP\Domain\Repositories;

use MiniERP\Domain\Entities\Product;

interface ProductRepositoryInterface
{
    public function save(Product $product): Product;
    public function update(Product $product): bool;
    public function findById(int $id): ?Product;
    public function findAll(): array;
    public function delete(int $id): bool;
}
