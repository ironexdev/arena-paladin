<?php

namespace Paladin\Service\User;

use Paladin\Exception\Client\InvalidAuthorizationTokenException;
use Paladin\Exception\Client\UserNotFoundException;
use Paladin\Model\Document\AuthorizationToken;
use Paladin\Model\Document\User;

interface UserServiceInterface
{
    public function isUnique(User $newUser): bool;

    /**
     * @throws InvalidAuthorizationTokenException
     * @throws UserNotFoundException
     */
    public function activateUser(string $authorizationTokenString);

    /**
     * @throws UserNotFoundException
     */
    public function fetchUserByAuthorizationToken(AuthorizationToken $authorizationToken): User;

    /**
     * @throws UserNotFoundException
     */
    public function fetchUserByEmail(string $email): User;

    /**
     * @throws UserNotFoundException
     */
    public function fetchUserById(string $id): User;
}
