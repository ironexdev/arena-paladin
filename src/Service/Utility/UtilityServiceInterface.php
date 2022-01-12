<?php

namespace Paladin\Service\Utility;

interface UtilityServiceInterface
{
    public function expirationTimestamp(int $seconds, string $dateTimeZone = "UTC"): int;
}
