<?php declare(strict_types=1);

namespace Paladin\Service\Authentication;

use JetBrains\PhpStorm\Pure;
use Paladin\Enum\InMemoryCacheNamespaceEnum;
use Paladin\Exception\Client\InactiveUserException;
use Paladin\Exception\Client\InvalidAuthorizationTokenException;
use Paladin\Exception\Client\InvalidPasswordException;
use Paladin\Exception\Client\UserNotFoundException;
use Paladin\Model\Document\AuthorizationToken;
use Paladin\Model\Document\User;
use Paladin\Model\Entity\AuthenticationToken;
use Paladin\Model\EntityFactory\AuthenticationToken\AuthenticationTokenFactoryInterface;
use Paladin\Service\Authorization\AuthorizationServiceInterface;
use Paladin\Service\Cache\InMemoryCacheServiceInterface;
use Paladin\Service\Cookie\CookieServiceInterface;
use Paladin\Service\Security\SecurityServiceInterface;
use Paladin\Service\User\UserServiceInterface;

class AuthenticationService implements AuthenticationServiceInterface
{
    public function __construct(
        private AuthenticationTokenFactoryInterface $authenticationTokenFactory,
        private AuthorizationServiceInterface       $authorizationService,
        private CookieServiceInterface              $cookieService,
        private InMemoryCacheServiceInterface       $inMemoryCacheService,
        private SecurityServiceInterface            $securityService,
        private UserServiceInterface                $userService,
        private ?AuthenticationToken                $authenticationToken,
        private ?User                               $user
    )
    {
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
        string $password,
        bool   $remember
    )
    {
        $user = $this->userService->fetchUserByEmail($email);

        if (!$this->securityService->passwordVerify($password, $user->getPassword())) {
            throw new InvalidPasswordException();
        }

        if (!$user->getActive()) {
            throw new InactiveUserException();
        }

        // Created here, because $validator has to be sent before it is hashed
        $selector = $this->createAuthenticationTokenSelector($user);
        $validator = $this->createAuthenticationTokenValidator();

        $authenticationToken = $this->createAuthenticationToken($selector, $validator);

        $this->cookieService->setAuthenticationToken($selector, $validator, $remember); // Without hashed validator
        $this->storeAuthenticationToken($authenticationToken); // With hashed validator
    }

    /**
     * @throws InvalidAuthorizationTokenException
     */
    public function loginWithoutPassword(
        string $authorizationTokenString,
        bool   $remember
    )
    {
        list($authorizationTokenSelector, $authorizationTokenValidator) = AuthorizationToken::getSelectorAndValidatorFromString($authorizationTokenString);

        $authorizationToken = $this->authorizationService->fetchAuthorizationTokenBySelector($authorizationTokenSelector);

        $this->authorizationService->validateAuthorizationToken($authorizationTokenValidator, $authorizationToken);

        try {
            $user = $this->userService->fetchUserByAuthorizationToken($authorizationToken);
        } catch (UserNotFoundException $e) {
            throw new InvalidAuthorizationTokenException($e->getMessage());
        }

        // Created here, because $validator has to be sent before it is hashed
        $authenticationTokenSelector = $this->createAuthenticationTokenSelector($user);
        $authenticationTokenValidator = $this->createAuthenticationTokenValidator();

        $authenticationToken = $this->createAuthenticationToken($authenticationTokenSelector, $authenticationTokenValidator);

        $this->cookieService->setAuthenticationToken($authenticationTokenSelector, $authenticationTokenValidator, $remember); // Without hashed validator
        $this->storeAuthenticationToken($authenticationToken); // With hashed validator
    }

    public function logout()
    {
        $this->destroyAuthenticationToken();
        $this->cookieService->unsetAuthenticationToken();
    }

    #[Pure] public function getUser(): ?User
    {
        return $this->user;
    }

    private function createAuthenticationToken(
        string $selector,
        string $validator
    ): AuthenticationToken
    {
        $hashedValidator = $this->securityService->hash(
            "sha256",
            $validator
        );

        return $this->authenticationTokenFactory->create($selector, $hashedValidator);
    }

    private function destroyAuthenticationToken()
    {
        // TODO log if redis delete fails?
        $this->inMemoryCacheService->delete(
            InMemoryCacheNamespaceEnum::AUTHENTICATION_TOKEN,
            $this->authenticationToken->getSelector()
        );
    }

    private function storeAuthenticationToken(AuthenticationToken $authenticationToken)
    {
        $this->inMemoryCacheService->set(
            InMemoryCacheNamespaceEnum::AUTHENTICATION_TOKEN,
            $authenticationToken->getSelector(),
            $authenticationToken->getValidator()
        );
    }

    #[Pure] private function createAuthenticationTokenSelector(User $user): string
    {
        return $user->getId();
    }

    private function createAuthenticationTokenValidator(): string
    {
        return $this->securityService->bin2hex($this->securityService->randomBytes(32));
    }
}
