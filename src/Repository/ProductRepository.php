<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Repository;

use Doctrine\DBAL\Connection;
use Raketa\BackendTestTask\Domain\Product;
use Exception;

final readonly class ProductRepository
{

    public function __construct(private Connection $connection)
    {
    }

    public function getByUuid(string $uuid): Product
    {
        $rows = $this->getByUuids([$uuid]);

        if (empty($row)) {
            throw new Exception('Product not found');
        }

        return $row[0];
    }

    public function getByUuids(array $uuids): array
    {
        return array_map(
            static fn (array $row): Product => $this->make($row),
            $this->connection->fetchAllAssociative(
                "SELECT * FROM products WHERE uuid IN (:uuids)",
                [
                    'uuids' => $uuids,
                ],
                [
                    'uuids' => \Doctrine\DBAL\ArrayParameterType::STRING
                ]
            )
        );
    }

    public function getByCategory(string $category): array
    {
        return array_map(
            static fn (array $row): Product => $this->make($row),
            $this->connection->fetchAllAssociative(
                "SELECT * FROM products WHERE is_active = 1 AND category = :category",
                [
                    'category' => $category,
                ]
            )
        );
    }

    public function make(array $row): Product
    {
        return new Product(
            $row['id'],
            $row['uuid'],
            $row['is_active'],
            $row['category'],
            $row['name'],
            $row['description'],
            $row['thumbnail'],
            $row['price'],
        );
    }
}
