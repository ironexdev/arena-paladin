<?php

namespace Paladin\Model\Repository\AuthenticationToken;

use Paladin\Model\Document\AuthenticationToken;
use Paladin\Model\Document\User;

interface AuthenticationTokenRepositoryInterface
{
    public function deleteByUser(User $user);

    public function persist(AuthenticationToken $authenticationToken): AuthenticationToken;

}
