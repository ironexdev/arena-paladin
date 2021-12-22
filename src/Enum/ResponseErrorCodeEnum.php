<?php

namespace Paladin\Enum;

use MyCLabs\Enum\Enum;

class ResponseErrorCodeEnum extends Enum
{
    const XSRF = "{{xsrf}}"; // Invalid XSRF token
}
