<?php declare(strict_types=1);

namespace Paladin\Api\GraphQL\User\Input;

use TheCodingMachine\GraphQLite\Annotations\Factory;

class ActivateUserInputFactory
{
    #[Factory]
    public function create(
        string $authorizationCode
    ): ActivateUserInput
    {
        $activateUserInput = new ActivateUserInput();

        $activateUserInput->setAuthorizationCode($authorizationCode);

        return $activateUserInput;
    }
}
