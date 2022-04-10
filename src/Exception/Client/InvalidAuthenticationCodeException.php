<?php

namespace Paladin\Exception\Client;

class InvalidAuthenticationCodeException extends AbstractClientException
{
    protected $message = "Invalid Authentication Code";
}
