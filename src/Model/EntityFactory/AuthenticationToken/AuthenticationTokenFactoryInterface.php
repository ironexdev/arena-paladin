<?php

namespace Paladin\Model\EntityFactory\AuthenticationToken;

use Paladin\Model\Entity\AuthenticationToken;

interface AuthenticationTokenFactoryInterface
{
    public function create(string $selector, string $validator): AuthenticationToken;
}
