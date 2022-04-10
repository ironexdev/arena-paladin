<?php

namespace Paladin\Service\User;

use Doctrine\ODM\MongoDB\DocumentManager;
use Paladin\Exception\Client\InvalidAuthorizationCodeException;
use Paladin\Exception\Client\UserNotFoundException;
use Paladin\Model\Document\AuthorizationToken;
use Paladin\Model\Document\User;
use Paladin\Model\Repository\User\UserRepositoryInterface;
use Paladin\Service\Authorization\AuthorizationServiceInterface;

class UserService implements UserServiceInterface
{
    public function __construct(
        private AuthorizationServiceInterface $authorizationService,
        private DocumentManager               $dm,
        private UserRepositoryInterface       $userRepository
    )
    {
    }

    /**
     * @throws InvalidAuthorizationCodeException
     * @throws UserNotFoundException
     */
    public function activateUser(string $authorizationCode)
    {
        list($selector, $validator) = AuthorizationToken::parseAuthorizationCode($authorizationCode);

        $authorizationToken = $this->authorizationService->fetchAuthorizationTokenBySelector(
            $selector
        );

        $this->authorizationService->validateAuthorizationToken(
            $validator,
            $authorizationToken
        );

        $user = $this->fetchUserByAuthorizationToken($authorizationToken);
        $user->setActive(true);

        $this->dm->persist($user);
    }

    public function isUnique(User $newUser): bool
    {
        return $this->userRepository->isUnique($newUser);
    }

    /**
     * @throws UserNotFoundException
     */
    public function fetchUserByAuthorizationToken(AuthorizationToken $authorizationToken): User
    {
        /** @var ?User $user */
        $user = $this->userRepository->findOneBy(["id" => $authorizationToken->getUser()->getId()]);

        if (!$user) {
            throw new UserNotFoundException("User provided by Authorization Token was not found");
        }

        return $user;
    }

    /**
     * @throws UserNotFoundException
     */
    public function fetchUserByEmail(string $email): User
    {
        /** @var ?User $user */
        $user = $this->userRepository->findOneBy(["email" => $email]);

        if (!$user) {
            throw new UserNotFoundException("User with given e-mail was not found");
        }

        return $user;
    }

    /**
     * @throws UserNotFoundException
     */
    public function fetchUserById(string $id): User
    {
        /** @var ?User $user */
        $user = $this->userRepository->findOneBy(["id" => $id]);

        if (!$user) {
            throw new UserNotFoundException("User with given id was not found");
        }

        return $user;
    }
}
