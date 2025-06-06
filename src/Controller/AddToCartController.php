<?php

namespace Raketa\BackendTestTask\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Raketa\BackendTestTask\Domain\CartItem;
use Raketa\BackendTestTask\Repository\CartManager;
use Raketa\BackendTestTask\Repository\ProductRepository;
use Raketa\BackendTestTask\Utils\Http\Response\JsonResponse;
use Raketa\BackendTestTask\View\CartView;
use Ramsey\Uuid\Uuid;
use Exception;

readonly class AddToCartController
{
    public function __construct(
        private ProductRepository $productRepository,
        private CartView $cartView,
        private CartManager $cartManager,
    ) {
    }

    public function post(RequestInterface $request): ResponseInterface
    {
        $response = new JsonResponse();

        try {
            $rawRequest = json_decode($request->getBody()->getContents(), true);
            $product = $this->productRepository->getByUuid($rawRequest['productUuid']);
            if(!$product) {
                throw new Exception('Product not found');
            }

            if(!$product->isActive()) {
                throw new Exception('Product is not active');
            }

            $cart = $this->cartManager->getCart();
            $cart->addItem(new CartItem(
                Uuid::uuid4()->toString(),
                $product->getUuid(),
                $product->getPrice(),
                $rawRequest['quantity'],
            ));

            $this->cartManager->saveCart($cart);

            $response->getBody()->write(
                json_encode(
                    [
                        'status' => 'success',
                        'cart' => $this->cartView->toArray($cart)
                    ],
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                )
            );
        } catch (Exception $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]));
            $response->withStatus(404);
        }

        return $response;
    }
}
