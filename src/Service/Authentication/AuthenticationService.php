<?php declare(strict_types=1);

namespace Paladin\Service\Authentication;

use JetBrains\PhpStorm\Pure;
use Paladin\Exception\Client\InactiveUserException;
use Paladin\Exception\Client\InvalidAuthorizationCodeException;
use Paladin\Exception\Client\InvalidPasswordException;
use Paladin\Exception\Client\UserNotFoundException;
use Paladin\Model\Document\AuthenticationToken;
use Paladin\Model\Document\AuthorizationToken;
use Paladin\Model\Document\User;
use Paladin\Model\DocumentFactory\AuthenticationToken\AuthenticationTokenFactoryInterface;
use Paladin\Model\Repository\AuthenticationToken\AuthenticationTokenRepositoryInterface;
use Paladin\Service\Authorization\AuthorizationServiceInterface;
use Paladin\Service\Security\SecurityServiceInterface;
use Paladin\Service\User\UserServiceInterface;

class AuthenticationService implements AuthenticationServiceInterface
{
    public function __construct(
        private AuthenticationTokenFactoryInterface    $authenticationTokenFactory,
        private AuthenticationTokenRepositoryInterface $authenticationTokenRepository,
        private AuthorizationServiceInterface          $authorizationService,
        private SecurityServiceInterface               $securityService,
        private UserServiceInterface                   $userService,
        private ?User                                  $user
    )
    {
    }

    public function createAuthenticationToken(string $selector, string $validator, User $user): AuthenticationToken
    {
        $hashedValidator = $this->securityService->hash(
            "sha256",
            $validator
        );

        return $this->authenticationTokenFactory->create(
            $selector,
            $hashedValidator,
            $user
        );
    }

    public function createAuthenticationTokenSelector(): string
    {
        return $this->securityService->bin2hex($this->securityService->randomBytes(16));
    }

    public function createAuthenticationTokenValidator(): string
    {
        return $this->securityService->bin2hex($this->securityService->randomBytes(32));
    }

    #[Pure] public function isAuthenticated(): bool
    {
        return (bool)$this->user;
    }

    #[Pure] public function isLogged(): bool
    {
        return $this->isAuthenticated();
    }

    /**
     * @throws InvalidPasswordException
     * @throws InactiveUserException
     * @throws UserNotFoundException
     */
    public function login(
        string $email,
        string $password
    ): User
    {
        $user = $this->userService->fetchUserByEmail($email);

        if (!$this->securityService->passwordVerify($password, $user->getPassword())) {
            throw new InvalidPasswordException();
        }

        if (!$user->getActive()) {
            throw new InactiveUserException();
        }

        return $user;
    }

    /**
     * This functions accepts authorization token (sent via e-mail as a link) and returns authentication token
     * @throws InvalidAuthorizationCodeException
     * @throws InactiveUserException
     */
    public function loginWithoutPassword(
        string $authorizationCode
    ): User
    {
        list($authorizationTokenSelector, $authorizationTokenValidator) = AuthorizationToken::parseAuthorizationCode($authorizationCode);

        $authorizationToken = $this->authorizationService->fetchAuthorizationTokenBySelector($authorizationTokenSelector);

        $this->authorizationService->validateAuthorizationToken($authorizationTokenValidator, $authorizationToken);

        try {
            $user = $this->userService->fetchUserByAuthorizationToken($authorizationToken);
        } catch (UserNotFoundException $e) {
            throw new InvalidAuthorizationCodeException($e->getMessage());
        }

        if (!$user->getActive()) {
            throw new InactiveUserException();
        }

        return $user;
    }

    public function logout(User $user)
    {
        $this->deleteAuthenticationToken($user);
    }

    #[Pure] public function getUser(): ?User
    {
        return $this->user;
    }

    private function deleteAuthenticationToken(User $user)
    {
        $this->authenticationTokenRepository->deleteByUser($user);
    }
}
