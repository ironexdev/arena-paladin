<?php

namespace Paladin\Exception\Client;

class UserAlreadyExistsException extends AbstractClientException
{
    protected $message = "User Already Exists";
}
