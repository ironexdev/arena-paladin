<?php

namespace Paladin\Exception\Client;

class UserNotFoundException extends AbstractClientException
{
    protected $message = "User Not Found";
}
