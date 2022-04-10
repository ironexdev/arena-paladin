<?php declare(strict_types=1);

namespace Paladin\Service\Authorization;

use Paladin\Exception\Client\InvalidAuthorizationCodeException;
use Paladin\Model\Document\AuthorizationToken;
use Paladin\Model\Document\User;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface as GraphQLiteAuthorizationServiceInterface;

interface AuthorizationServiceInterface extends GraphQLiteAuthorizationServiceInterface
{
    public function createAuthorizationToken(
        string $selector,
        string $validator,
        string $action,
        User   $user
    ): AuthorizationToken;

    public function createAuthorizationTokenSelector(): string;

    public function createAuthorizationTokenValidator(): string;

    /**
     * @throws InvalidAuthorizationCodeException
     */
    public function fetchAuthorizationTokenBySelector(string $selector): AuthorizationToken;

    /**
     * @throws InvalidAuthorizationCodeException
     */
    public function validateAuthorizationToken(string $validator, AuthorizationToken $authorizationToken);
}
