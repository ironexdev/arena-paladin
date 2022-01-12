<?php declare(strict_types=1);

namespace Paladin\Api\GraphQL\Authentication\Input;

use TheCodingMachine\GraphQLite\Annotations\Factory;

class LoginWithoutPasswordInputFactory
{
    #[Factory]
    public function create(
        string $authorizationToken,
        bool   $remember = false,
    ): LoginWithoutPasswordInput
    {
        $loginWithoutPasswordInput = new LoginWithoutPasswordInput();

        $loginWithoutPasswordInput->setAuthorizationToken($authorizationToken);
        $loginWithoutPasswordInput->setRemember($remember);

        return $loginWithoutPasswordInput;
    }
}
