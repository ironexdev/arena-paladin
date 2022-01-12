<?php declare(strict_types=1);

namespace Paladin\Api\GraphQL\User\Input;

use TheCodingMachine\GraphQLite\Annotations\Factory;

class CreateUserInputFactory
{
    #[Factory]
    public function create(
        string $firstName,
        string $lastName,
        string $nickname,
        string $email,
        string $password
    ): CreateUserInput
    {
        $userInput = new CreateUserInput();
        $userInput->setFirstName($firstName);
        $userInput->setLastName($lastName);
        $userInput->setNickname($nickname);
        $userInput->setEmail($email);
        $userInput->setPassword($password);

        return $userInput;
    }
}
