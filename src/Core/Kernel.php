<?php declare(strict_types=1);

namespace Paladin\Core;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Kernel
{
    public function __construct(
        ServerRequestInterface  $serverRequest,
        RequestHandlerInterface $middlewareStack
    )
    {
        $response = $middlewareStack->handle($serverRequest);

        $this->sendResponse($response->getStatusCode(), $response->getHeaders(), $response->getBody());
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
}
