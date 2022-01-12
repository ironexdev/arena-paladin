<?php declare(strict_types=1);

namespace Paladin\Api\GraphQL\Authorization;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Error;
use Paladin\Enum\AuthorizationActionEnum;
use Paladin\Enum\LoggerEnum;
use Paladin\Enum\ResponseStatusCodeEnum;
use Paladin\Exception\Client\UserNotFoundException;
use Paladin\Service\Authorization\AuthorizationServiceInterface;
use Paladin\Service\Mailer\MailerServiceInterface;
use Paladin\Service\User\UserServiceInterface;
use TheCodingMachine\GraphQLite\Annotations\Autowire;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use Paladin\Api\GraphQL\AbstractController;
use Paladin\Api\GraphQL\Authorization\Input\CreateAuthorizationTokenInput;

class AuthorizationController extends AbstractController
{
    #[Mutation]
    public function createAuthorizationToken(
        CreateAuthorizationTokenInput $createAuthorizationTokenInput,
                                      #[Autowire] AuthorizationServiceInterface $authorizationService,
                                      #[Autowire] DocumentManager $dm,
                                      #[Autowire] MailerServiceInterface $mailerService,
                                      #[Autowire] UserServiceInterface $userService
    ): bool
    {
        $this->validateInput($createAuthorizationTokenInput);

        $email = $createAuthorizationTokenInput->getEmail();
        $action = $createAuthorizationTokenInput->getAction();

        try {
            $user = $userService->fetchUserByEmail($email);
        } catch (UserNotFoundException $e) {
            $this->logger->info(LoggerEnum::AUTHORIZATION_TOKEN_CREATE_FAILED, ["exception" => $e]);
            return true;
        }

        // Created here, because $validator has to be sent before it is hashed
        $selector = $authorizationService->createAuthorizationTokenSelector();
        $validator = $authorizationService->createAuthorizationTokenValidator();

        $authorizationToken = $authorizationService->createAuthorizationToken($selector, $validator, $action, $user);

        try {
            $dm->persist($authorizationToken);
            $dm->flush();
        } catch (MongoDBException $e) {
            throw new Error(LoggerEnum::AUTHORIZATION_TOKEN_CREATE_FAILED, ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR, $e);
        }

        if ($action === AuthorizationActionEnum::LOGIN_WITHOUT_PASSWORD) {
            $mailerService->sendLoginWithoutPasswordAuthorizationEmail($user->getEmail(), $selector . ":" . $validator, $createAuthorizationTokenInput->getRemember());
        }

        return true;
    }
}
