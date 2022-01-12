<?php

namespace Paladin\Service\Cookie;

use Paladin\Enum\DateTimeEnum;
use Paladin\Enum\EnvironmentEnum;
use Paladin\Service\Utility\UtilityServiceInterface;

class CookieService implements CookieServiceInterface
{
    private const AUTHENTICATION_TOKEN = "authenticationToken";

    public function __construct(private UtilityServiceInterface $utilityService)
    {}

    private function secure(): bool
    {
        return ENVIRONMENT !== EnvironmentEnum::DEVELOPMENT;
    }

    public function export(): array
    {
        return $_COOKIE;
    }

    /**
     * @return string|null
     */
    public function getAuthenticationToken(): ?string
    {
        return $_COOKIE[static::AUTHENTICATION_TOKEN] ?? null;
    }

    public function setAuthenticationToken(string $selector, string $validator, bool $remember): void
    {
        $expiration = $remember ? $this->utilityService->expirationTimestamp(DateTimeEnum::WEEK) : 0;

        setcookie(
            static::AUTHENTICATION_TOKEN,
            $selector . ":" . $validator,
            $expiration,
            "/",
            CLIENT_DOMAIN,
            static::secure(),
            false
        );
    }

    public function unsetAuthenticationToken()
    {
        setcookie(static::AUTHENTICATION_TOKEN, "", -1, "/", CLIENT_DOMAIN);
    }
}
