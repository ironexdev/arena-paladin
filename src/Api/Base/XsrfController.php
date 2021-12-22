<?php declare(strict_types=1);

namespace Paladin\Api\Base;

use Paladin\Core\Session;
use Paladin\Enum\ResponseHeaderEnum;
use Paladin\Security\SecurityServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class XsrfController extends AbstractController
{
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param SecurityServiceInterface $securityService
     * @return ResponseInterface
     */
    public function read(
        ServerRequestInterface   $request,
        ResponseInterface        $response,
        SecurityServiceInterface $securityService
    ): ResponseInterface
    {
        $xsrfToken = Session::getXsrfToken();

        if (!$xsrfToken) {
            $xsrfToken = $securityService->xsrfToken();
            Session::setXsrfToken($xsrfToken);
        }

        return $this->jsonResponse((object)["status" => true], $response)
            ->withHeader(ResponseHeaderEnum::XSRF_TOKEN, $xsrfToken);
    }
}
