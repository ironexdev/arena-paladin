<?php

namespace Paladin\Service\Mailer;

interface MailerServiceInterface
{
    public function sendLoginWithoutPasswordAuthorizationEmail(
        string $to,
        string $authorizationTokenString,
        bool   $remember
    );

    public function sendRegistrationUserAlreadyExistsEmail(string $to);

    public function sendActivateUserAuthorizationEmail(string $to, string $authorizationTokenString);
}
