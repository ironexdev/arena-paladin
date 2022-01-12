<?php declare(strict_types=1);

namespace Paladin\Api\GraphQL\Authentication;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Error;
use Paladin\Enum\GraphqlExceptionCategoryEnum;
use Paladin\Enum\LoggerEnum;
use Paladin\Enum\ResponseStatusCodeEnum;
use Paladin\Enum\TranslatorEnum;
use Paladin\Exception\Client\InactiveUserException;
use Paladin\Exception\Client\InvalidAuthorizationTokenException;
use Paladin\Exception\Client\InvalidPasswordException;
use Paladin\Exception\Client\UserNotFoundException;
use Paladin\Service\Authentication\AuthenticationServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TheCodingMachine\GraphQLite\Annotations\Autowire;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;
use Paladin\Api\GraphQL\AbstractController;
use Paladin\Api\GraphQL\Authentication\Input\LoginInput;
use Paladin\Api\GraphQL\Authentication\Input\LoginWithoutPasswordInput;

class AuthenticationController extends AbstractController
{
    /**
     * @throws GraphQLException
     */
    #[Mutation]
    public function login(
        LoginInput $loginInput,
                   #[Autowire] AuthenticationServiceInterface $authenticationService,
                   #[Autowire] DocumentManager $dm,
                   #[Autowire] TranslatorInterface $translator
    ): bool
    {
        $this->validateInput($loginInput);
        $email = $loginInput->getEmail();
        $remember = $loginInput->getRemember();
        $password = $loginInput->getPassword();

        try {
            $authenticationService->login($email, $password, $remember);
        } catch (InvalidPasswordException|UserNotFoundException $e) {
            $this->logger->info(LoggerEnum::LOGIN_FAILED, ["exception" => $e]);
            throw new GraphQLException($translator->trans(TranslatorEnum::INVALID_EMAIL_OR_PASSWORD), ResponseStatusCodeEnum::FORBIDDEN);
        } catch (InactiveUserException $e) {
            $this->logger->info(LoggerEnum::LOGIN_FAILED, ["exception" => $e]);
            throw new GraphQLException(
                $translator->trans(TranslatorEnum::INACTIVE_USER),
                ResponseStatusCodeEnum::BAD_REQUEST,
                null,
                GraphqlExceptionCategoryEnum::EXCEPTION_MESSAGE);
        }

        try {
            $dm->flush();
        } catch (MongoDBException $e) {
            throw new Error(LoggerEnum::LOGIN_FAILED, ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR, $e);
        }

        return true;
    }

    /**
     * This method is used after user clicks on verification link in e-mail,
     * it is not used after submitting Login Without Password form
     * @throws GraphQLException
     */
    #[Mutation]
    public function loginWithoutPassword(
        LoginWithoutPasswordInput $loginWithoutPasswordInput,
                                  #[Autowire] AuthenticationServiceInterface $authenticationService,
                                  #[Autowire] DocumentManager $dm,
                                  #[Autowire] TranslatorInterface $translator
    ): bool
    {
        $this->validateInput($loginWithoutPasswordInput);
        $remember = $loginWithoutPasswordInput->getRemember();
        $authorizationTokenString = $loginWithoutPasswordInput->getAuthorizationToken();

        try {
            $authenticationService->loginWithoutPassword($authorizationTokenString, $remember);
        } catch (InvalidAuthorizationTokenException $e) {
            $this->logger->info(LoggerEnum::LOGIN_WITHOUT_PASSWORD_FAILED, ["exception" => $e]);
            throw new GraphQLException($translator->trans(TranslatorEnum::INVALID_AUTHORIZATION_CODE), ResponseStatusCodeEnum::FORBIDDEN);
        }

        try {
            $dm->flush();
        } catch (MongoDBException $e) {
            throw new Error(LoggerEnum::LOGIN_WITHOUT_PASSWORD_FAILED, ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR, $e);
        }

        return true;
    }

    #[Mutation]
    public function logout(
        #[Autowire] AuthenticationServiceInterface $authenticationService
    ): bool
    {
        $authenticationService->logout();

        return true;
    }
}
