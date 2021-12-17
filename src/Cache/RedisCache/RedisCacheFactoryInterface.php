<?php declare(strict_types=1);

namespace Paladin\Cache\RedisCache;

interface RedisCacheFactoryInterface
{
    public function create(string $namespace): RedisCacheInterface;
}