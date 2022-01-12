<?php declare(strict_types=1);

namespace Paladin\Api\REST;

use Paladin\Service\Authentication\AuthenticationServiceInterface;
use Paladin\Service\Cookie\CookieServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IndexController extends AbstractController
{
    public function read(
        ServerRequestInterface         $request,
        ResponseInterface              $response,
        AuthenticationServiceInterface $authenticationService,
        CookieServiceInterface         $cookieService
    ): ResponseInterface
    {
        return $this->jsonResponse((object)[
            "user" => $authenticationService->getUser()?->getId(),
            "cookie" => $cookieService->export()
        ], $response);
    }
}
