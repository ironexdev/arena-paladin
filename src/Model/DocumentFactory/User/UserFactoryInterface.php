<?php

namespace Paladin\Model\DocumentFactory\User;

use Paladin\Api\GraphQL\User\Input\CreateUserInput;
use Paladin\Model\Document\User;

interface UserFactoryInterface
{
    public function create(
        string $firstName,
        string $lastName,
        string $nickname,
        string $email,
        string $password
    ): User;

    public function createFromInput(CreateUserInput $userInput): User;
}
