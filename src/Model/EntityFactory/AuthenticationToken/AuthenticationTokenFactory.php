<?php

namespace Paladin\Model\EntityFactory\AuthenticationToken;

use Paladin\Model\Entity\AuthenticationToken;

class AuthenticationTokenFactory implements AuthenticationTokenFactoryInterface
{
    public function create(string $selector, string $validator): AuthenticationToken
    {
        $authenticationToken = new AuthenticationToken();
        $authenticationToken->setSelector($selector);
        $authenticationToken->setValidator($validator);

        return $authenticationToken;
    }
}
