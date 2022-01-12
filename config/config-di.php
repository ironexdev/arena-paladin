<?php

use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use MongoDB\Client;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Logger as MonologLogger;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Paladin\Cache\FilesystemCache\FilesystemCacheFactory;
use Paladin\Cache\FilesystemCache\FilesystemCacheFactoryInterface;
use Paladin\Core\MiddlewareStack\MiddlewareStack;
use Paladin\Core\MiddlewareStack\MiddlewareStackInterface;
use Paladin\Core\Router;
use Paladin\Enum\ContentTypeEnum;
use Paladin\Enum\EnvironmentEnum;
use Paladin\Enum\InMemoryCacheNamespaceEnum;
use Paladin\Enum\LocaleEnum;
use Paladin\Enum\RequestMethodEnum;
use Paladin\Enum\ResponseStatusCodeEnum;
use Paladin\Exception\Client\UserNotFoundException;
use Paladin\Model\Document\AuthorizationToken;
use Paladin\Model\Document\User;
use Paladin\Model\DocumentFactory\AuthorizationToken\AuthorizationTokenFactory;
use Paladin\Model\DocumentFactory\AuthorizationToken\AuthorizationTokenFactoryInterface;
use Paladin\Model\DocumentFactory\User\UserFactory;
use Paladin\Model\DocumentFactory\User\UserFactoryInterface;
use Paladin\Model\Entity\AuthenticationToken;
use Paladin\Model\EntityFactory\AuthenticationToken\AuthenticationTokenFactory;
use Paladin\Model\EntityFactory\AuthenticationToken\AuthenticationTokenFactoryInterface;
use Paladin\Model\Repository\AuthorizationToken\AuthorizationTokenRepositoryInterface;
use Paladin\Model\Repository\User\UserRepositoryInterface;
use Paladin\Service\Authentication\AuthenticationService;
use Paladin\Service\Authentication\AuthenticationServiceInterface;
use Paladin\Service\Authorization\AuthorizationService;
use Paladin\Service\Authorization\AuthorizationServiceInterface;
use Paladin\Service\Cache\InMemoryCacheService;
use Paladin\Service\Cache\InMemoryCacheServiceInterface;
use Paladin\Service\Cookie\CookieService;
use Paladin\Service\Cookie\CookieServiceInterface;
use Paladin\Service\Mailer\MailerService;
use Paladin\Service\Mailer\MailerServiceInterface;
use Paladin\Service\Security\SecurityService;
use Paladin\Service\Security\SecurityServiceInterface;
use Paladin\Service\User\UserService;
use Paladin\Service\User\UserServiceInterface;
use Paladin\Service\Utility\UtilityService;
use Paladin\Service\Utility\UtilityServiceInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TheCodingMachine\GraphQLite\Http\Psr15GraphQLMiddlewareBuilder;
use TheCodingMachine\GraphQLite\Http\WebonyxGraphqlMiddleware;
use TheCodingMachine\GraphQLite\SchemaFactory;
use Tuupola\Middleware\CorsMiddleware;

return [
    /* Custom Interfaces *****************************************************/
    /*************************************************************************/
    MiddlewareStackInterface::class => DI\factory(function (
        ResponseInterface        $defaultResponse,
        CorsMiddleware           $corsMiddleware,
        WebonyxGraphqlMiddleware $graphQLMiddleware,
        Router                   $router
    ): MiddlewareStackInterface {
        return new MiddlewareStack(
            $defaultResponse->withStatus(ResponseStatusCodeEnum::NOT_FOUND), // default/fallback response
            $corsMiddleware,
            $graphQLMiddleware,
            $router
        );
    }),
    // Services
    AuthenticationServiceInterface::class => DI\factory(function (
        AuthenticationTokenFactoryInterface $authenticationTokenFactory,
        AuthorizationServiceInterface       $authorizationService,
        CookieServiceInterface              $cookieService,
        InMemoryCacheServiceInterface       $inMemoryCacheService,
        SecurityService                     $securityService,
        UserServiceInterface                $userService
    ) {
        $authenticationTokenString = $cookieService->getAuthenticationToken();
        $authenticationToken = null;
        $user = null;

        if ($authenticationTokenString) {
            list($selector, $validator) = AuthenticationToken::getSelectorAndValidatorFromString($authenticationTokenString);

            if ($selector && $validator) {
                // TODO encrypt/decrypt selector
                $storedValidator = $inMemoryCacheService->get(InMemoryCacheNamespaceEnum::AUTHENTICATION_TOKEN, $selector);

                if ($storedValidator && $securityService->hashEquals(
                        $storedValidator,
                        $securityService->hash("sha256", $validator) // Validator stored in a Cookie is not hashed
                    )) {

                    try {
                        $user = $userService->fetchUserById($selector); // Selector === FetchUserResponse id
                        $authenticationToken = $authenticationTokenFactory->create($user->getId(), $validator);
                    } catch (UserNotFoundException $e) {
                    }
                    // TODO remove all tokens from in memory cache?
                }
            }
        }

        return new AuthenticationService(
            $authenticationTokenFactory,
            $authorizationService,
            $cookieService,
            $inMemoryCacheService,
            $securityService,
            $userService,
            $authenticationToken,
            $user
        );
    }),
    AuthorizationServiceInterface::class => DI\autowire(AuthorizationService::class),
    CookieServiceInterface::class => DI\autowire(CookieService::class),
    InMemoryCacheServiceInterface::class => DI\factory((function () {
        return new InMemoryCacheService("redis://" . REDIS_HOST . ":" . REDIS_PORT . "/0");
    })),
    MailerServiceInterface::class => DI\autowire(MailerService::class),
    SecurityServiceInterface::class => DI\autowire(SecurityService::class),
    UserServiceInterface::class => DI\autowire(UserService::class),
    UtilityServiceInterface::class => DI\autowire(UtilityService::class),

    // Factories
    AuthenticationTokenFactoryInterface::class => DI\autowire(AuthenticationTokenFactory::class),
    AuthorizationTokenFactoryInterface::class => DI\autowire(AuthorizationTokenFactory::class),
    UserFactoryInterface::class => DI\autowire(UserFactory::class),

    // Repositories
    AuthorizationTokenRepositoryInterface::class => DI\factory(function (DocumentManager $dm) {
        return $dm->getRepository(AuthorizationToken::class);
    }),
    UserRepositoryInterface::class => DI\factory(function (DocumentManager $dm) {
        return $dm->getRepository(User::class);
    }),

    // Implements PSR-15
    Router::class => DI\factory(function (ContainerInterface $container, ResponseFactoryInterface $responseFactory) {
        $routes = require_once(APP_DIRECTORY . DS . ".." . DS . "config" . DS . "api" . DS . "rest" . DS . "routes.php");
        return new Router($container, $responseFactory, $routes);
    }),

    // Implements PSR-16
    FilesystemCacheFactoryInterface::class => DI\create(FilesystemCacheFactory::class)->constructor(APP_DIRECTORY . DS . ".." . DS . "var" . DS . "cache"),

    // PSR interfaces ********************************************************/
    /*************************************************************************/

    // PSR-3
    LoggerInterface::class => DI\factory(function () {
        $logger = new Logger("debug");
        $fileHandler = new StreamHandler(DEBUG_LOG, MonologLogger::DEBUG);
        $formatter = new JsonFormatter();
        $formatter->includeStacktraces(false);
        $fileHandler->setFormatter($formatter);
        $logger->pushHandler($fileHandler);

        return $logger;
    }),

    // PSR-7
    ResponseInterface::class => DI\factory(function (ResponseFactoryInterface $responseFactory) {
        return $responseFactory->createResponse();
    }),
    ResponseFactoryInterface::class => DI\autowire(Psr17Factory::class),

    // PSR-15
    ServerRequestInterface::class => DI\factory(function (
        ServerRequestFactoryInterface $serverRequestFactory,
        UriFactoryInterface           $uriFactory,
        UploadedFileFactoryInterface  $uploadedFileFactory,
        StreamFactoryInterface        $streamFactory
    ) {
        $creator = new ServerRequestCreator(
            $serverRequestFactory,
            $uriFactory,
            $uploadedFileFactory,
            $streamFactory
        );

        $serverRequest = $creator->fromGlobals();

        $contentType = $serverRequest->getHeaderLine("Content-Type");

        // Parse request body, because Nyholm\Psr7Server doesn't parse JSON requests
        if ($contentType === ContentTypeEnum::JSON) {
            if (!$serverRequest->getParsedBody()) {
                $content = $serverRequest->getBody()->getContents();
                $data = json_decode($content, true);

                if ($data === false || json_last_error() !== JSON_ERROR_NONE) {
                    throw new InvalidArgumentException(json_last_error_msg() . " in body: '" . $content . "'");
                }

                $serverRequest = $serverRequest->withParsedBody($data);
            }
        }

        return $serverRequest;
    }),

    // PSR-17
    ServerRequestFactoryInterface::class => DI\create(Psr17Factory::class),
    StreamFactoryInterface::class => DI\create(Psr17Factory::class),
    UriFactoryInterface::class => DI\create(Psr17Factory::class),
    UploadedFileFactoryInterface::class => DI\create(Psr17Factory::class),

    // 3rd party Interfaces and Classes **************************************/
    /*************************************************************************/

    // Tuupola
    CorsMiddleware::class => DI\factory(function (LoggerInterface $logger) {
        $allowedOrigins = [CLIENT_URL];

        return new CorsMiddleware([
            "origin" => $allowedOrigins,
            "methods" => RequestMethodEnum::toArray(),
            "headers.allow" => explode(",", ACCESS_CONTROL_ALLOW_HEADERS),
            "headers.expose" => explode(",", ACCESS_CONTROL_EXPOSE_HEADERS),
            "credentials" => ACCESS_CONTROL_ALLOW_CREDENTIALS === "true",
            "cache" => 0,
            // TODO extract to a separate file and only log errors if possible
            // "logger" => $logger
        ]);
    }),

    // Doctrine
    DocumentManager::class => DI\factory(function () {
        $dbPassword = file_get_contents(MONGO_PASSWORD_FILE);
        $uri = "mongodb://" . MONGO_USER . ":" . $dbPassword . "@" . MONGO_HOST . ":" . MONGO_PORT . "/" . MONGO_INITDB_DATABASE;
        $client = new Client($uri, [], ["typeMap" => DocumentManager::CLIENT_TYPEMAP]);
        $config = new Configuration();

        $modelDirectory = APP_DIRECTORY . DS . "Model";
        $config->setProxyDir(APP_DIRECTORY . DS . ".." . DS . "var" . DS . "cache" . DS . "doctrine" . DS . "proxy");
        $config->setProxyNamespace("Paladin\\Model\\Proxy");
        $config->setHydratorDir(APP_DIRECTORY . DS . ".." . DS . "var" . DS . "cache" . DS . "doctrine" . DS . "hydrator");
        $config->setHydratorNamespace("Paladin\\Model\\Hydrator");
        $config->setDefaultDB(MONGO_INITDB_DATABASE);
        $config->setMetadataDriverImpl(AnnotationDriver::create($modelDirectory . DS . "Document"));

        if (ENVIRONMENT === EnvironmentEnum::DEVELOPMENT) {
            $config->setAutoGenerateProxyClasses(Configuration::AUTOGENERATE_EVAL);
            $config->setAutoGenerateHydratorClasses(Configuration::AUTOGENERATE_EVAL);
        } else {
            $config->setAutoGenerateProxyClasses(Configuration::AUTOGENERATE_FILE_NOT_EXISTS);
            $config->setAutoGenerateHydratorClasses(Configuration::AUTOGENERATE_FILE_NOT_EXISTS);
        }

        // spl_autoload_register is necessary to autoload generated proxy classes. Without this, the proxy library would re-generate proxy classes for every request
        spl_autoload_register($config->getProxyManagerConfiguration()->getProxyAutoloader());

        return DocumentManager::create($client, $config);
    }),

    // Symfony
    MailerInterface::class => DI\factory(function (LoggerInterface $logger) {
        $password = rtrim(file_get_contents(MAILER_PASSWORD));
        $smtpTransport = Transport::fromDsn(
            "smtp://" . MAILER_USER . ":" . $password . "@" . MAILER_HOST . ":" . MAILER_PORT,
            null,
            null,
            $logger
        );

        return new Mailer($smtpTransport);
    }),
    TranslatorInterface::class => DI\Factory(function (JsonFileLoader $jsonFileLoader) {
        $acceptLanguage = $_SERVER["HTTP_ACCEPT_LANGUAGE"] ?? null;
        $locale = $acceptLanguage ? Locale::acceptFromHttp($acceptLanguage) : DEFAULT_LOCALE;

        if (!in_array($locale, SUPPORTED_LOCALES)) {
            $locale = DEFAULT_LOCALE;
        }

        $translator = new Translator($locale);
        $translator->addLoader("json", $jsonFileLoader);
        $translator->addResource(
            "json",
            APP_DIRECTORY . DS . ".." . DS . "translations" . DS . "messages+intl-icu." . $locale . ".json",
            $locale,
            "messages+intl-icu"
        );

        return $translator;
    }),
    ValidatorInterface::class => DI\factory(function (TranslatorInterface $translator) {
        return Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->setTranslator($translator)
            ->getValidator();
    }),

    // TheCodingMachine, implements PSR-15
    WebonyxGraphqlMiddleware::class => DI\factory(function (
        ContainerInterface              $container,
        FilesystemCacheFactoryInterface $filesystemCacheFactory,
        ResponseFactoryInterface        $responseFactory,
        StreamFactoryInterface          $streamFactory
    ) {
        $filesystemCache = $filesystemCacheFactory->create("graphql");

        $schemaFactory = new SchemaFactory($filesystemCache, $container);

        $schemaFactory->addControllerNamespace("Paladin\\Api\\GraphQL\\")
            ->addTypeNamespace("Paladin\\Model\\Document\\")
            ->addTypeNamespace("Paladin\\Api\\GraphQL\\");

        $schema = $schemaFactory->createSchema();

        $builder = new Psr15GraphQLMiddlewareBuilder($schema);
        $builder->setUrl("/graphql");
        $builder->setResponseFactory($responseFactory);
        $builder->setStreamFactory($streamFactory);

        return $builder->createMiddleware();
    }),
];
