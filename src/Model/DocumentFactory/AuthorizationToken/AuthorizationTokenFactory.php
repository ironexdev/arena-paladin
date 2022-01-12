<?php declare(strict_types=1);

namespace Paladin\Model\DocumentFactory\AuthorizationToken;

use Paladin\Model\Document\AuthorizationToken;
use Paladin\Model\Document\User;

class AuthorizationTokenFactory implements AuthorizationTokenFactoryInterface
{
    public function create(string $selector, string $validator, string $action, User $user): AuthorizationToken
    {
        $authorizationToken = new AuthorizationToken();
        $authorizationToken->setAction($action);
        $authorizationToken->setActive(true);
        $authorizationToken->setSelector($selector);
        $authorizationToken->setValidator($validator);
        $authorizationToken->setUser($user);

        return $authorizationToken;
    }
}
