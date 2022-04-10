<?php

namespace Paladin\Service\Cache;

use Error;
use Paladin\Enum\InMemoryCacheNamespaceEnum;
use Paladin\Enum\ResponseStatusCodeEnum;
use Psr\Cache\CacheException as Psr6CacheException;
use Psr\SimpleCache\CacheException as SimpleCacheException;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;

class InMemoryCacheService implements InMemoryCacheServiceInterface
{
    private ?Cacheinterface $defaultCache = null;

    public function __construct(private string $dsn)
    {
    }

    public function delete(string $namespace, string $key): bool
    {
        try {
            return $this->getCacheByNamespace($namespace)->delete($key);
        } catch (SimpleCacheException|Psr6CacheException $e) {
            throw new Error("InMemory cache error", ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR, $e);
        }
    }

    public function get(string $namespace, string $key): mixed
    {
        try {
            return $this->getCacheByNamespace($namespace)->get($key);
        } catch (SimpleCacheException|Psr6CacheException $e) {
            throw new Error("InMemory cache error", ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR, $e);
        }
    }

    public function set(string $namespace, string $key, $value, int $ttl = null)
    {
        try {
            $this->getCacheByNamespace($namespace)->set($key, $value, $ttl);
        } catch (SimpleCacheException|Psr6CacheException $e) {
            throw new Error("InMemory cache error", ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR, $e);
        }
    }

    private function defaultCache(): Cacheinterface
    {
        if (!$this->defaultCache) {
            $client = RedisAdapter::createConnection(
                $this->dsn
            );

            $this->defaultCache = new Psr16Cache(
                new RedisAdapter($client, InMemoryCacheNamespaceEnum::DEFAULT)
            );
        }

        return $this->defaultCache;
    }

    private function getCacheByNamespace(string $namespace): Cacheinterface
    {
        switch ($namespace) {
            default:
            {
                return $this->defaultCache();
            }
        }
    }
}
