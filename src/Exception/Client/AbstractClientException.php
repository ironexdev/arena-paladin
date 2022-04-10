<?php declare(strict_types=1);

namespace Paladin\Exception\Client;

use Exception;
use JetBrains\PhpStorm\Pure;
use Paladin\Enum\ResponseStatusCodeEnum;

abstract class AbstractClientException extends Exception
{
    // Client exceptions should always be caught and transformed before the response is sent
    #[Pure] public function __construct(string $message = "")
    {
        parent::__construct($message ?: $this->message, ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR, null);
    }
}
