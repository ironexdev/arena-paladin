<?php

namespace Paladin\Model\Repository\AuthorizationToken;

use Paladin\Model\Document\AuthorizationToken;

interface AuthorizationTokenRepositoryInterface
{
    public function persist(AuthorizationToken $authorizationToken): AuthorizationToken;
}
