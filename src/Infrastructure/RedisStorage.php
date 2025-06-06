<?php

namespace Raketa\BackendTestTask\Infrastructure;

use Raketa\BackendTestTask\Infrastructure\Contract\StorageInterface;
use Raketa\BackendTestTask\Utils\Exception\ConnectorException;
use Redis;
use RedisException;

class RedisStorage implements StorageInterface {

    private Redis $redis;

    public function __construct($host, $port, $password, $dbIndex) {
        $this->redis = new Redis();

        try {
            $isConnected = $this->redis->isConnected();
            if (!$isConnected && $this->redis->ping('Pong')) {
                $isConnected = $this->redis->connect(
                    $host,
                    $port,
                );
            }
        } catch (RedisException) {
        }

        if ($isConnected) {
            $this->redis->auth($password);
            $this->redis->select($dbindex);
        }
    }

    public function get(string $key)
    {
        try {
            return $this->redis->get($key);
        } catch (RedisException $e) {
            throw new ConnectorException('Connector error', $e->getCode(), $e);
        }
    }

    public function set(string $key, mixed $value, int $ttl = 24 * 60 * 60)
    {
        try {
            if(is_object($value)) {
                $this->redis->setex($key, $ttl, serialize($value));
            } else {
                $this->redis->setex($key, $ttl, $value);
            }
        } catch (RedisException $e) {
            throw new ConnectorException('Connector error', $e->getCode(), $e);
        }
    }

    public function has(string $key): bool
    {
        return $this->redis->exists($key);
    }
}