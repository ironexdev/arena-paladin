<?php declare(strict_types=1);

namespace Paladin\Enum;

use MyCLabs\Enum\Enum;

class TranslatorEnum extends Enum
{
    // Validation
    const INACTIVE_USER = "inactive_user";
    const INVALID_AUTHORIZATION_CODE = "invalid_authorization_code";
    const INVALID_EMAIL_FORMAT = "invalid_email_format";
    const INVALID_EMAIL_OR_PASSWORD = "invalid_email_or_password";
    const INVALID_PASSWORD_FORMAT = "invalid_password_format";
    const STRING_MAX_LENGTH = "string_max_length";
    const STRING_MIN_LENGTH = "string_min_length";
    CONST ONLY_LETTERS = "only_letters";

    // E-mail
    const EMAIL_ACCOUNT_ALREADY_EXISTS_SUBJECT = "email_account_already_exists_subject";
    const EMAIL_ACCOUNT_ALREADY_EXISTS_BODY = "email_account_already_exists_body";
    const EMAIL_ACTIVATE_USER_SUBJECT = "email_activate_user_subject";
    const EMAIL_ACTIVATE_USER_BODY = "email_activate_user_body";
    const EMAIL_LOGIN_AUTHORIZATION_SUBJECT = "email_login_authorization_subject";
    const EMAIL_LOGIN_AUTHORIZATION_BODY = "email_login_authorization_body";
}
