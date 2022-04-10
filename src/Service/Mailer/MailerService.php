<?php

namespace Paladin\Service\Mailer;

use Error;
use Paladin\Enum\AuthorizationActionEnum;
use Paladin\Enum\LoggerEnum;
use Paladin\Enum\ResponseStatusCodeEnum;
use Paladin\Enum\TranslatorEnum;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailerService implements MailerServiceInterface
{
    public function __construct(
        private LoggerInterface     $logger,
        private MailerInterface     $mailer,
        private TranslatorInterface $translator
    )
    {
    }

    public function sendLoginWithoutPasswordAuthorizationEmail(
        string $to,
        string $authorizationCode
    )
    {
        $email = new Email();

        $authorizationLink = CLIENT_URL .
            "/authorization" . // TODO translate this
            "?code=" . $authorizationCode .
            "&action=" . AuthorizationActionEnum::LOGIN_WITHOUT_PASSWORD;

        $html = $this->translator->trans(TranslatorEnum::EMAIL_LOGIN_AUTHORIZATION_BODY, [
            "authorizationLink" => $authorizationLink
        ]);
        $email->from(ADMIN_EMAIL)
            ->to($to)
            ->subject(TranslatorEnum::EMAIL_LOGIN_AUTHORIZATION_SUBJECT)
            ->html($html)
            ->text($this->htmlToText($html));

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(LoggerEnum::SEND_EMAIL_FAILED, [
                "recipient" => $to, "type" => "LoginWithoutPasswordAuthorizationEmail was not sent."
            ]);

            throw new Error($e->getMessage(), ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR, $e);
        }
    }

    public function sendRegistrationUserAlreadyExistsEmail(
        string $to,
    )
    {
        $email = new Email();

        $loginLink = CLIENT_URL .
            "/login"; // TODO translate this

        $html = $this->translator->trans(TranslatorEnum::EMAIL_ACCOUNT_ALREADY_EXISTS_BODY, [
            "loginLink" => $loginLink
        ]);

        $email->from(ADMIN_EMAIL)
            ->to($to)
            ->subject(TranslatorEnum::EMAIL_ACCOUNT_ALREADY_EXISTS_SUBJECT)
            ->html($html)
            ->text($this->htmlToText($html));

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(LoggerEnum::SEND_EMAIL_FAILED, [
                "recipient" => $to, "type" => "RegistrationUserAlreadyExistsEmail was not sent."
            ]);

            throw new Error($e->getMessage(), ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR, $e);
        }
    }

    public function sendActivateUserAuthorizationEmail(string $to, string $authorizationCode)
    {
        $email = new Email();

        $authorizationLink = CLIENT_URL .
            "/authorization" .
            "?code=" . $authorizationCode .
            "&action=" . AuthorizationActionEnum::ACTIVATE_USER;

        $html = $this->translator->trans(TranslatorEnum::EMAIL_ACTIVATE_USER_BODY, [
            "authorizationLink" => $authorizationLink
        ]);

        $email->from(ADMIN_EMAIL)
            ->to($to)
            ->subject(TranslatorEnum::EMAIL_ACTIVATE_USER_SUBJECT)
            ->html($html)
            ->text($this->htmlToText($html));

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(LoggerEnum::SEND_EMAIL_FAILED, [
                "recipient" => $to, "type" => "ActivateUserAuthorizationEmail was not sent."
            ]);

            throw new Error($e->getMessage(), ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR, $e);
        }
    }

    private function htmlToText(string $html): string
    {
        return strip_tags(str_replace("<br>", "\n", $html));
    }
}
