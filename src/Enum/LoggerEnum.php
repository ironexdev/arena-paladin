<?php

namespace Paladin\Enum;

use MyCLabs\Enum\Enum;

class LoggerEnum extends Enum
{
    const SEND_EMAIL_FAILED = "send_email_failed";
    const LOGIN_FAILED = "login_failed";
    const LOGIN_WITHOUT_PASSWORD_FAILED = "login_without_password_failed";
    const AUTHENTICATION_TOKEN_CREATE_FAILED = "authentication_token_create_failed";
    const AUTHORIZATION_TOKEN_CREATE_FAILED = "authorization_token_create_failed";
    const ACTIVATE_USER_FAILED = "activate_user_failed";
    const CREATE_USER_FAILED = "create_user_failed";
}
