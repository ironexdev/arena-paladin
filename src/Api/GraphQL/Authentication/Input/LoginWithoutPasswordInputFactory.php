<?php declare(strict_types=1);

namespace Paladin\Api\GraphQL\Authentication\Input;

use TheCodingMachine\GraphQLite\Annotations\Factory;

class LoginWithoutPasswordInputFactory
{
    #[Factory]
    public function create(
        string $authorizationCode,
    ): LoginWithoutPasswordInput
    {
        $loginWithoutPasswordInput = new LoginWithoutPasswordInput();

        $loginWithoutPasswordInput->setAuthorizationCode($authorizationCode);

        return $loginWithoutPasswordInput;
    }
}
