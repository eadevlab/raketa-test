<?php

namespace Raketa\BackendTestTask\Infrastructure\Contract;

interface StorageInterface
{
    public function get(string $key);
    public function set(string $key, mixed $value, int $ttl = 0);
    public function has(string $key): bool;
}