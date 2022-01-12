<?php

namespace Paladin\Model\Repository\User;

use Paladin\Model\Document\User;

interface UserRepositoryInterface
{
    public function activate(User $user): void;

    public function isUnique(User $user): bool;
}
