<?php declare(strict_types=1);

namespace Paladin\Model\DocumentFactory\AuthenticationToken;

use Paladin\Model\Document\AuthenticationToken;
use Paladin\Model\Document\User;

interface AuthenticationTokenFactoryInterface
{
    public function create(string $selector, string $validator, User $user): AuthenticationToken;
}
