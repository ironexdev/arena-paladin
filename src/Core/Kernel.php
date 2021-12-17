<?php declare(strict_types=1);

namespace Paladin\Core;

use Error;
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
     */
    public function __construct(
        CorsMiddleware                   $corsMiddleware,
        WebonyxGraphqlMiddleware         $graphQLMiddleware,
        ResponseInterface                $defaultResponse,
        Router                           $router,
        ServerRequestInterface           $serverRequest,
        private SecurityServiceInterface $securityService
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

        $response = $middlewareStack->handle($serverRequest);

        // Add XSRF token to response for all GET requests
        if ($serverRequest->getMethod() === RequestMethodEnum::GET) {
            $response = $this->addXsrfToken($serverRequest, $response);
        }

        return $response;
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
                throw new Error("Invalid XSRF Token '" . $requestXsrfToken . "' for session '" . session_id() . "'.", ResponseStatusCodeEnum::FORBIDDEN);
            }
        }
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    private function addXsrfToken(ServerRequestInterface $serverRequest, ResponseInterface $response): ResponseInterface
    {
        $xsrfToken = Session::getXsrfToken();

        if (!$xsrfToken) {
            $xsrfToken = $this->securityService->xsrfToken();
            Session::setXsrfToken($xsrfToken);
        }

        return $response
            ->withHeader(ResponseHeaderEnum::ACCESS_CONTROL_ALLOW_HEADERS, ResponseHeaderEnum::XSRF_TOKEN)
            ->withHeader(ResponseHeaderEnum::XSRF_TOKEN, $xsrfToken);
    }
}
