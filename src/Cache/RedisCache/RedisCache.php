<?php declare(strict_types=1);

namespace Paladin\Cache\RedisCache;

use Symfony\Component\Cache\Psr16Cache;

class RedisCache extends Psr16Cache implements RedisCacheInterface
{

}