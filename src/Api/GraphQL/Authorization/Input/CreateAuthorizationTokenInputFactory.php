<?php declare(strict_types=1);

namespace Paladin\Api\GraphQL\Authorization\Input;

use TheCodingMachine\GraphQLite\Annotations\Factory;

class CreateAuthorizationTokenInputFactory
{
    #[Factory]
    public function create(
        string $email,
        string $action,
    ): CreateAuthorizationTokenInput
    {
        $createAuthorizationTokenInput = new CreateAuthorizationTokenInput();
        $createAuthorizationTokenInput->setEmail($email);
        $createAuthorizationTokenInput->setAction($action);

        return $createAuthorizationTokenInput;
    }
}
