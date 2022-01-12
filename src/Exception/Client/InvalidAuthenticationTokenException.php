<?php

namespace Paladin\Exception\Client;

class InvalidAuthenticationTokenException extends AbstractClientException
{
    protected $message = "Invalid Authentication Token";
}
