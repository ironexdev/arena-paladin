<?php declare(strict_types=1);

namespace Paladin\Enum;

use MyCLabs\Enum\Enum;

class AuthorizationActionEnum extends Enum
{
    public const ACTIVATE_USER = "activate_user";
    public const LOGIN_WITHOUT_PASSWORD = "login_without_password";
}
