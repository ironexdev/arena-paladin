<?php declare(strict_types=1);

namespace Paladin\Api\GraphQL\Authentication\Input;

use TheCodingMachine\GraphQLite\Annotations\Factory;

class LoginInputFactory
{
    #[Factory]
    public function create(
        string $email,
        string $password,
        bool   $remember = false
    ): LoginInput
    {
        $loginInput = new LoginInput();

        $loginInput->setEmail($email);
        $loginInput->setPassword($password);
        $loginInput->setRemember($remember);

        return $loginInput;
    }
}
