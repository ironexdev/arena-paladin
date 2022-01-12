<?php declare(strict_types=1);

namespace Paladin\Service\Authorization;

use Paladin\Exception\Client\InvalidAuthorizationTokenException;
use Paladin\Model\Document\AuthorizationToken;
use Paladin\Model\Document\User;
use Paladin\Model\DocumentFactory\AuthorizationToken\AuthorizationTokenFactoryInterface;
use Paladin\Model\Repository\AuthorizationToken\AuthorizationTokenRepositoryInterface;
use Paladin\Service\Security\SecurityServiceInterface;

class AuthorizationService implements AuthorizationServiceInterface
{
    /**
     * @param AuthorizationTokenFactoryInterface $authorizationTokenFactory
     * @param AuthorizationTokenRepositoryInterface $authorizationTokenRepository
     * @param SecurityServiceInterface $securityService
     */
    public function __construct(
        private AuthorizationTokenFactoryInterface    $authorizationTokenFactory,
        private AuthorizationTokenRepositoryInterface $authorizationTokenRepository,
        private SecurityServiceInterface              $securityService
    )
    {
    }

    public function createAuthorizationToken(
        string $selector,
        string $validator,
        string $action,
        User   $user
    ): AuthorizationToken
    {
        $hashedValidator = $this->securityService->hash(
            "sha256",
            $validator
        );

        return $this->authorizationTokenFactory->create(
            $selector,
            $hashedValidator,
            $action,
            $user
        );
    }

    /**
     * @throws InvalidAuthorizationTokenException
     */
    public function fetchAuthorizationTokenBySelector(string $selector): AuthorizationToken
    {
        /** @var ?AuthorizationToken $authorizationToken */
        $authorizationToken = $this->authorizationTokenRepository->findOneBy(["selector" => $selector]);

        if (!$authorizationToken) {
            throw new InvalidAuthorizationTokenException("Authorization Token not found");
        }

        return $authorizationToken;
    }

    public function isAllowed(string $right, $subject = null): bool
    {
        // TODO
        return false;
    }

    /**
     * @throws InvalidAuthorizationTokenException
     */
    public function validateAuthorizationToken(string $validator, AuthorizationToken $authorizationToken)
    {
        $validator = $this->securityService->hash("sha256", $validator); // Validator stored in a Cookie is not hashed
        $storedAuthorizationTokenValidator = $authorizationToken->getValidator();

        $validHash = $this->securityService->hashEquals($storedAuthorizationTokenValidator, $validator);

        if (!$validHash) {
            throw new InvalidAuthorizationTokenException("Authorization Token is not valid");
        }
    }

    public function createAuthorizationTokenSelector(): string
    {
        return $this->securityService->bin2hex($this->securityService->randomBytes(16));
    }

    public function createAuthorizationTokenValidator(): string
    {
        return $this->securityService->bin2hex($this->securityService->randomBytes(32));
    }
}
