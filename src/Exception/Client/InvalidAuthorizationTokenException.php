<?php

namespace Paladin\Exception\Client;

class InvalidAuthorizationTokenException extends AbstractClientException
{
    protected $message = "Invalid Authorization Token";
}
