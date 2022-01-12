<?php declare(strict_types=1);

namespace Paladin\Model\DocumentFactory\User;

use Paladin\Api\GraphQL\User\Input\CreateUserInput;
use Paladin\Model\Document\User;
use Paladin\Service\Security\SecurityServiceInterface;

class UserFactory implements UserFactoryInterface
{
    public function __construct(private SecurityServiceInterface $securityService)
    {
    }

    public function create(
        string $firstName,
        string $lastName,
        string $nickname,
        string $email,
        string $password
    ): User
    {
        $user = new User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setNickname($nickname);
        $user->setEmail($email);
        $user->setPassword(
            $this->securityService->passwordHash($password)
        );

        return $user;
    }

    public function createFromInput(CreateUserInput $userInput): User
    {
        $user = new User();
        $user->setFirstName($userInput->getFirstName());
        $user->setLastName($userInput->getLastName());
        $user->setNickname($userInput->getNickname());
        $user->setEmail($userInput->getEmail());
        $user->setPassword(
            $this->securityService->passwordHash($userInput->getPassword())
        );

        return $user;
    }
}
