<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Raketa\BackendTestTask\Repository\CartManager;
use Raketa\BackendTestTask\Utils\Http\Response\JsonResponse;
use Raketa\BackendTestTask\View\CartView;

readonly class GetCartController
{
    public function __construct(
        public CartView $cartView,
        public CartManager $cartManager
    ) {
    }

    public function get(RequestInterface $request): ResponseInterface
    {
        $response = new JsonResponse();
        $cart = $this->cartManager->getCart();

        $statusCode = 200;
        if (!$cart) {
            $response->getBody()->write(
                json_encode(
                    ['message' => 'Cart not found']
                )
            );
            $statusCode = 404;
        } else {
            $response->getBody()->write(
                json_encode(
                    $this->cartView->toArray($cart)
                )
            );
        }
        return $response
            ->withStatus($statusCode);
    }
}
