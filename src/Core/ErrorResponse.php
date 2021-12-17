<?php

namespace Paladin\Core;

use Paladin\Enum\ResponseStatusCodeEnum;
use Throwable;

class ErrorResponse
{
    /**
     * @param Throwable $e
     * @param int|null $responseCode
     */
    public static function json(Throwable $e, int $responseCode = null)
    {
        $responseCode = $responseCode ?? $e->getCode() ?? ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR;

        http_response_code($responseCode);

        echo json_encode([
            "errors" => [
                "message" => $e->getMessage()
            ]
        ]);
    }
}