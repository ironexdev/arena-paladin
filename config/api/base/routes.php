<?php

use Paladin\Api\Base\XsrfController;
use Paladin\Api\Base\IndexController;
use Paladin\Api\Base\LoginController;
use Paladin\Api\Base\LogoutController;
use Paladin\Enum\RequestMethodEnum;

return [
    "/" => [
        RequestMethodEnum::GET => [
            "handler" => IndexController::class . "::read"
        ]
    ],
    "/login" => [
        RequestMethodEnum::POST => [
            "handler" => LoginController::class . "::create"
        ]
    ],
    "/logout" => [
        RequestMethodEnum::DELETE => [
            "handler" => LogoutController::class . "::delete"
        ]
    ],
    "/xsrf" => [
        RequestMethodEnum::GET => [
            "handler" => XsrfController::class . "::read"
        ]
    ]
];
