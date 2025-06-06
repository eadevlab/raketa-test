<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\View;

use Raketa\BackendTestTask\Domain\Cart;
use Raketa\BackendTestTask\Domain\CartItem;
use Raketa\BackendTestTask\Repository\ProductRepository;

readonly class CartView
{
    public function __construct(
        private ProductRepository $productRepository
    ) {
    }

    public function toArray(Cart $cart): array
    {
        $data = [
            'uuid' => $cart->getUuid(),
            'payment_method' => $cart->getPaymentMethod(),
            'customer' => [],
            'items' => [],
        ];

        if($cart->getCustomer()) {
            $data['customer'] = [
                'id' => $cart->getCustomer()->getId(),
                'name' => implode(' ', [
                    $cart->getCustomer()->getLastName(),
                    $cart->getCustomer()->getFirstName(),
                    $cart->getCustomer()->getMiddleName(),
                ]),
                'email' => $cart->getCustomer()->getEmail(),
            ];
        }

        $productsList = $this->productRepository->getByUuids(
            array_map( fn(CartItem $item): string => $item->getProductUuid(), $cart->getItems())
        );
        $products = [];
        foreach ($productsList as $product) {
            $products[$product->getUuid()] = $product;
        }
        unset($productsList);

        $total = 0;

        foreach ($cart->getItems() as $item) {
            $total += $item->getPrice() * $item->getQuantity();
            $product = $products[$item->getProductUuid()] ?? null;
            if (!$product) {
                continue;
            }

            $data['items'][] = [
                'uuid' => $item->getUuid(),
                'price' => $item->getPrice(),
                'total' => $item->getPrice() * $item->getQuantity(),
                'quantity' => $item->getQuantity(),
                'product' => [
                    'id' => $product->getId(),
                    'uuid' => $product->getUuid(),
                    'name' => $product->getName(),
                    'thumbnail' => $product->getThumbnail(),
                    'price' => $product->getPrice(),
                ],
            ];
        }

        $data['total'] = $total;

        return $data;
    }
}
