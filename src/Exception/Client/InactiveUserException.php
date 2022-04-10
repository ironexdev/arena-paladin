<?php

namespace Paladin\Exception\Client;

use Paladin\Enum\TranslatorEnum;

class InactiveUserException extends AbstractClientException
{
    protected $message = TranslatorEnum::INACTIVE_USER;
}
