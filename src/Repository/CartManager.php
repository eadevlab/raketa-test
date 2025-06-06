<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Repository;

use Exception;
use Psr\Log\LoggerInterface;
use Raketa\BackendTestTask\Domain\Cart;
use Raketa\BackendTestTask\Infrastructure\Contract\StorageInterface;

readonly final class CartManager
{
    public function __construct(
        private StorageInterface $storage,
        private LoggerInterface  $logger
    )
    {
    }

    public function saveCart(Cart $cart)
    {
        try {
            $this->storage->set(session_id(), $cart);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @return Cart
     */
    public function getCart(): Cart
    {
        try {
            if($this->storage->has(session_id())) {
                return unserialize($this->storage->get(session_id()));
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return new Cart(session_id());
    }
}
