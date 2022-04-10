<?php declare(strict_types=1);

namespace Paladin\Api\GraphQL\Authentication;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Error;
use Exception;
use Paladin\Api\GraphQL\AbstractController;
use Paladin\Api\GraphQL\Authentication\Input\LoginInput;
use Paladin\Api\GraphQL\Authentication\Input\LoginWithoutPasswordInput;
use Paladin\Api\GraphQL\Authentication\Output\LoginOutput;
use Paladin\Api\GraphQL\Authentication\Output\LoginOutputFactory;
use Paladin\Api\GraphQL\Authentication\Output\LoginWithoutPasswordOutput;
use Paladin\Api\GraphQL\Authentication\Output\LoginWithoutPasswordOutputFactory;
use Paladin\Enum\GraphqlExceptionCategoryEnum;
use Paladin\Enum\LoggerEnum;
use Paladin\Enum\ResponseStatusCodeEnum;
use Paladin\Enum\TranslatorEnum;
use Paladin\Exception\Client\InactiveUserException;
use Paladin\Exception\Client\InvalidAuthorizationCodeException;
use Paladin\Exception\Client\InvalidPasswordException;
use Paladin\Exception\Client\UserNotFoundException;
use Paladin\Service\Authentication\AuthenticationServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TheCodingMachine\GraphQLite\Annotations\Autowire;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;

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
                   #[Autowire] LoginOutputFactory $loginOutputFactory,
                   #[Autowire] TranslatorInterface $translator
    ): LoginOutput
    {
        $this->validateInput($loginInput);
        $email = $loginInput->getEmail();
        $password = $loginInput->getPassword();

        try {
            $user = $authenticationService->login($email, $password);
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

        $selector = $authenticationService->createAuthenticationTokenSelector();
        $validator = $authenticationService->createAuthenticationTokenValidator();

        $authenticationToken = $authenticationService->createAuthenticationToken($selector, $validator, $user); // With hashed validator

        try {
            $dm->persist($authenticationToken);
            $dm->flush();
        } catch (MongoDBException $e) {
            throw new Error(LoggerEnum::AUTHENTICATION_TOKEN_CREATE_FAILED, ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR, $e);
        }

        return $loginOutputFactory->create(
            $selector . ":" . $validator // Without hashed validator
        );
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
                                  #[Autowire] LoginWithoutPasswordOutputFactory $loginWithoutPasswordOutputFactory,
                                  #[Autowire] TranslatorInterface $translator
    ): LoginWithoutPasswordOutput
    {
        $this->validateInput($loginWithoutPasswordInput);
        $authorizationCode = $loginWithoutPasswordInput->getAuthorizationCode();

        try {
            $user = $authenticationService->loginWithoutPassword($authorizationCode);
        } catch (InvalidAuthorizationCodeException $e) {
            $this->logger->info(LoggerEnum::LOGIN_WITHOUT_PASSWORD_FAILED, ["exception" => $e]);
            throw new GraphQLException($translator->trans(TranslatorEnum::INVALID_AUTHORIZATION_CODE), ResponseStatusCodeEnum::FORBIDDEN);
        }

        $selector = $authenticationService->createAuthenticationTokenSelector();
        $validator = $authenticationService->createAuthenticationTokenValidator();

        $authenticationToken = $authenticationService->createAuthenticationToken($selector, $validator, $user); // With hashed validator

        try {
            $dm->persist($authenticationToken);
            $dm->flush();
        } catch (MongoDBException $e) {
            throw new Error(LoggerEnum::AUTHENTICATION_TOKEN_CREATE_FAILED, ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR, $e);
        }

        return $loginWithoutPasswordOutputFactory->create(
            $selector . ":" . $validator // Without hashed validator
        );
    }

    /**
     * @throws GraphQLException
     */
    #[Mutation]
    public function logout(
        #[Autowire] AuthenticationServiceInterface $authenticationService,
        #[Autowire] TranslatorInterface $translator
    ): bool
    {
        $user = $authenticationService->getUser();

        if (!$user) {
            header("WWW-Authenticate: Bearer");
            throw new GraphQLException($translator->trans(TranslatorEnum::UNAUTHENTICATED), ResponseStatusCodeEnum::UNAUTHORIZED); // HTTP 401 has wrong name, it should be unauthenticated
        }

        $authenticationService->logout($user);

        return true;
    }
}
