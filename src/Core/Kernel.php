<?php declare(strict_types=1);

namespace Paladin\Core;

use Error;
use Paladin\Enum\ResponseErrorCodeEnum;
use Paladin\Exception\Http\ForbiddenException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use TheCodingMachine\GraphQLite\Http\WebonyxGraphqlMiddleware;
use Tuupola\Middleware\CorsMiddleware;
use Paladin\Enum\RequestHeaderEnum;
use Paladin\Enum\RequestMethodEnum;
use Paladin\Enum\ResponseHeaderEnum;
use Paladin\Enum\ResponseStatusCodeEnum;
use Paladin\Security\SecurityServiceInterface;

class Kernel
{
    /**
     * @param CorsMiddleware $corsMiddleware
     * @param WebonyxGraphqlMiddleware $graphQLMiddleware
     * @param ResponseInterface $defaultResponse
     * @param Router $router
     * @param ServerRequestInterface $serverRequest
     * @param SecurityServiceInterface $securityService
     * @throws ForbiddenException
     */
    public function __construct(
        CorsMiddleware                   $corsMiddleware,
        WebonyxGraphqlMiddleware         $graphQLMiddleware,
        ResponseInterface                $defaultResponse,
        Router                           $router,
        ServerRequestInterface           $serverRequest,
        SecurityServiceInterface $securityService
    )
    {
        Session::start();
        Session::regenerate();

        $this->validateXsrfToken($serverRequest, $securityService);

        $response = $this->processRequest($corsMiddleware, $graphQLMiddleware, $defaultResponse, $router, $serverRequest);

        $this->sendResponse($response->getStatusCode(), $response->getHeaders(), $response->getBody());
    }

    /**
     * @param CorsMiddleware $corsMiddleware
     * @param WebonyxGraphqlMiddleware $graphQLMiddleware
     * @param ResponseInterface $defaultResponse
     * @param Router $router
     * @param ServerRequestInterface $serverRequest
     * @return ResponseInterface
     */
    private function processRequest(
        CorsMiddleware           $corsMiddleware,
        WebonyxGraphqlMiddleware $graphQLMiddleware,
        ResponseInterface        $defaultResponse,
        Router                   $router,
        ServerRequestInterface   $serverRequest): ResponseInterface
    {
        $middlewareStack = new MiddlewareStack(
            $defaultResponse->withStatus(ResponseStatusCodeEnum::NOT_FOUND), // default/fallback response
            $corsMiddleware,
            $graphQLMiddleware,
            $router
        );

        return $middlewareStack->handle($serverRequest);
    }

    /**
     * @param int $code
     * @param array $headers
     * @param StreamInterface $body
     */
    private function sendResponse(int $code, array $headers, StreamInterface $body)
    {
        http_response_code($code);

        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                header(sprintf("%s: %s", $name, $value), false);
            }
        }

        echo $body;
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @param SecurityServiceInterface $securityService
     * @throws ForbiddenException
     */
    private function validateXsrfToken(ServerRequestInterface $serverRequest, SecurityServiceInterface $securityService): void
    {
        if (in_array($serverRequest->getMethod(), [
            RequestMethodEnum::DELETE,
            RequestMethodEnum::POST,
            RequestMethodEnum::PATCH,
            RequestMethodEnum::PUT
        ])) {
            $storedXsrfToken = Session::getXsrfToken();

            $requestXsrfToken = $serverRequest->getHeaderLine(RequestHeaderEnum::X_XSRF_TOKEN);

            if (!$storedXsrfToken || !$requestXsrfToken || !$securityService->hashEquals($storedXsrfToken, $requestXsrfToken)) {
                throw new ForbiddenException(
                    ResponseErrorCodeEnum::XSRF . " Invalid XSRF Token"
                );
            }
        }
    }
}
