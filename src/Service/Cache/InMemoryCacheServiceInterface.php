<?php

namespace Paladin\Service\Cache;

interface InMemoryCacheServiceInterface
{
    public function set(string $namespace, string $key, $value, int $ttl = null);

    public function get(string $namespace, string $key): mixed;

    public function delete(string $namespace, string $key): bool;
}
