<?php

namespace Paladin\Service\Authentication;

use Paladin\Exception\Client\InactiveUserException;
use Paladin\Exception\Client\InvalidAuthorizationCodeException;
use Paladin\Exception\Client\InvalidPasswordException;
use Paladin\Exception\Client\UserNotFoundException;
use Paladin\Model\Document\AuthenticationToken;
use Paladin\Model\Document\User;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface as GraphQLiteAuthenticationServiceInterface;

interface AuthenticationServiceInterface extends GraphQLiteAuthenticationServiceInterface
{
    public function createAuthenticationToken(string $selector, string $validator, User $user): AuthenticationToken;

    public function createAuthenticationTokenSelector(): string;

    public function createAuthenticationTokenValidator(): string;

    public function isAuthenticated(): bool;

    /**
     * @throws InvalidPasswordException
     * @throws InactiveUserException
     * @throws UserNotFoundException
     */
    public function login(
        string $email,
        string $password
    ): User;

    /**
     * @throws InvalidAuthorizationCodeException
     */
    public function loginWithoutPassword(
        string $authorizationCode
    ): User;

    public function logout(User $user);

    public function getUser(): ?User;
}
