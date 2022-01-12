<?php

use Paladin\Api\REST\IndexController;
use Paladin\Enum\RequestMethodEnum;

return [
    "/" => [
        RequestMethodEnum::GET => [
            "handler" => IndexController::class . "::read"
        ]
    ]
];
