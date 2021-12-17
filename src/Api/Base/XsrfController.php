<?php declare(strict_types=1);

namespace Paladin\Api\Base;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class XsrfController extends AbstractController
{
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function read(
        ServerRequestInterface   $request,
        ResponseInterface        $response
    ): ResponseInterface
    {
        return $this->jsonResponse((object)["status" => true], $response);
    }
}
