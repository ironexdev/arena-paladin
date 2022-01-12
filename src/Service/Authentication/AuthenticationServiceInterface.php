<?php

namespace Paladin\Service\Authentication;

use Paladin\Exception\Client\InactiveUserException;
use Paladin\Exception\Client\InvalidAuthorizationTokenException;
use Paladin\Exception\Client\InvalidPasswordException;
use Paladin\Exception\Client\UserNotFoundException;
use Paladin\Model\Document\User;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface as GraphQLiteAuthenticationServiceInterface;

interface AuthenticationServiceInterface extends GraphQLiteAuthenticationServiceInterface
{
    public function isAuthenticated(): bool;

    /**
     * @throws InvalidPasswordException
     * @throws InactiveUserException
     * @throws UserNotFoundException
     */
    public function login(
        string $email,
        string $password,
        bool   $remember
    );

    /**
     * @throws InvalidAuthorizationTokenException
     */
    public function loginWithoutPassword(
        string $authorizationTokenString,
        bool   $remember
    );

    public function logout();

    public function getUser(): ?User;
}
