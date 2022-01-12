<?php

namespace Paladin\Service\Cookie;

interface CookieServiceInterface
{
    public function export(): array;

    public function getAuthenticationToken(): ?string;

    public function setAuthenticationToken(string $selector, string $validator, bool $remember): void;

    public function unsetAuthenticationToken();
}
