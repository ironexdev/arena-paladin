<?php

namespace Paladin\Exception\Client;

class InvalidAuthorizationCodeException extends AbstractClientException
{
    protected $message = "Invalid Authorization Code";
}
