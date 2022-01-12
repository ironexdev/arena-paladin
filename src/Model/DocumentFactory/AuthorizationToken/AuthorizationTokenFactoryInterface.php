<?php declare(strict_types=1);

namespace Paladin\Model\DocumentFactory\AuthorizationToken;

use Paladin\Model\Document\AuthorizationToken;
use Paladin\Model\Document\User;

interface AuthorizationTokenFactoryInterface
{
    public function create(string $selector, string $validator, string $action, User $user): AuthorizationToken;
}
