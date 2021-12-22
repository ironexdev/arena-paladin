<?php

use Paladin\Api\Base\XsrfController;
use Paladin\Api\Base\IndexController;
use Paladin\Api\Base\AuthenticationController;
use Paladin\Enum\RequestMethodEnum;

return [
    "/" => [
        RequestMethodEnum::GET => [
            "handler" => IndexController::class . "::read"
        ]
    ],
    "/authentication" => [
        RequestMethodEnum::DELETE => [
            "handler" => AuthenticationController::class . "::delete"
        ],
        RequestMethodEnum::GET => [
            "handler" => AuthenticationController::class . "::read"
        ],
        RequestMethodEnum::POST => [
            "handler" => AuthenticationController::class . "::create"
        ]
    ],
    "/xsrf" => [
        RequestMethodEnum::GET => [
            "handler" => XsrfController::class . "::read"
        ]
    ]
];
