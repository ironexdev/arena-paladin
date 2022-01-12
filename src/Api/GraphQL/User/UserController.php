<?php declare(strict_types=1);

namespace Paladin\Api\GraphQL\User;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Error;
use Paladin\Api\GraphQL\AbstractController;
use Paladin\Api\GraphQL\User\Input\ActivateUserInput;
use Paladin\Enum\AuthorizationActionEnum;
use Paladin\Enum\LoggerEnum;
use Paladin\Enum\ResponseStatusCodeEnum;
use Paladin\Enum\TranslatorEnum;
use Paladin\Exception\Client\InvalidAuthorizationTokenException;
use Paladin\Exception\Client\UserNotFoundException;
use Paladin\Model\Document\User;
use Paladin\Model\DocumentFactory\User\UserFactoryInterface;
use Paladin\Model\Repository\User\UserRepositoryInterface;
use Paladin\Service\Authentication\AuthenticationServiceInterface;
use Paladin\Service\Authorization\AuthorizationServiceInterface;
use Paladin\Service\Mailer\MailerServiceInterface;
use Paladin\Service\User\UserServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TheCodingMachine\GraphQLite\Annotations\Autowire;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use Paladin\Api\GraphQL\User\Input\CreateUserInput;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;

class UserController extends AbstractController
{
    #[Mutation]
    public function createUser(
        CreateUserInput $createUserInput,
                        #[Autowire] AuthorizationServiceInterface $authorizationService,
                        #[Autowire] DocumentManager $dm,
                        #[Autowire] MailerServiceInterface $mailerService,
                        #[Autowire] UserFactoryInterface $userFactory,
                        #[Autowire] UserServiceInterface $userService
    ): bool
    {
        $this->validateInput($createUserInput);

        $user = $userFactory->createFromInput($createUserInput);

        if (!$userService->isUnique($user)) {
            $this->logger->info(LoggerEnum::CREATE_USER_FAILED, ["file" => __FILE__, "line" => __LINE__]);
            $mailerService->sendRegistrationUserAlreadyExistsEmail($user->getEmail());
            return true;
        }

        // Created here, because $validator has to be sent before it is hashed
        $selector = $authorizationService->createAuthorizationTokenSelector();
        $validator = $authorizationService->createAuthorizationTokenValidator();

        $authorizationToken = $authorizationService->createAuthorizationToken($selector, $validator, AuthorizationActionEnum::ACTIVATE_USER, $user);

        try {
            $dm->persist($authorizationToken);
            $dm->persist($user);
            $dm->flush();
        } catch (MongoDBException $e) {
            throw new Error(LoggerEnum::CREATE_USER_FAILED, ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR, $e);
        }

        $mailerService->sendActivateUserAuthorizationEmail($user->getEmail(), $selector . ":" . $validator);

        return true;
    }

    /**
     * This method is used after user clicks on verification link in e-mail,
     * it is not used after submitting Login Without Password form
     * @throws GraphQLException
     */
    #[Mutation]
    public function activateUser(
        ActivateUserInput $activateUserInput,
                          #[Autowire] DocumentManager $dm,
                          #[Autowire] TranslatorInterface $translator,
                          #[Autowire] UserServiceInterface $userService,
    ): bool
    {
        $this->validateInput($activateUserInput);
        $authorizationTokenString = $activateUserInput->getAuthorizationToken();

        try {
            $userService->activateUser($authorizationTokenString);
        } catch (InvalidAuthorizationTokenException|UserNotFoundException $e) {
            $this->logger->info(LoggerEnum::ACTIVATE_USER_FAILED, ["exception" => $e]);
            throw new GraphQLException($translator->trans(TranslatorEnum::INVALID_AUTHORIZATION_CODE), ResponseStatusCodeEnum::FORBIDDEN);
        }

        try {
            $dm->flush();
        } catch (MongoDBException $e) {
            throw new Error(LoggerEnum::ACTIVATE_USER_FAILED, ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR, $e);
        }

        return true;
    }

    #[Query]
    public function fetchCurrentUser(
        #[Autowire] AuthenticationServiceInterface $authenticationService,
        #[Autowire] DocumentManager $dm
    ): ?User
    {
        return $authenticationService->getUser();
    }
}
