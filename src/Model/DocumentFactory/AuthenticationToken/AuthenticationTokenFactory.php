<?php declare(strict_types=1);

namespace Paladin\Model\DocumentFactory\AuthenticationToken;

use Paladin\Model\Document\AuthenticationToken;
use Paladin\Model\Document\User;

class AuthenticationTokenFactory implements AuthenticationTokenFactoryInterface
{
    public function create(string $selector, string $validator, User $user): AuthenticationToken
    {
        $authenticationToken = new AuthenticationToken();
        $authenticationToken->setActive(true);
        $authenticationToken->setSelector($selector);
        $authenticationToken->setValidator($validator);
        $authenticationToken->setUser($user);

        return $authenticationToken;
    }
}
