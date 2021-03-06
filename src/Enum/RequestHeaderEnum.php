<?php declare(strict_types=1);

namespace Paladin\Enum;

use MyCLabs\Enum\Enum;

class RequestHeaderEnum extends Enum
{
    const ACCEPT = "Accept";
    const ACCEPT_LANGUAGE = "Accept-Language";
    const AUTHORIZATION = "Authorization";
    const CONTENT_LENGTH = "Content-Length";
    const CONTENT_TYPE = "Content-Type";
    const X_COUNTRY = "X-Country";
    const X_XSRF_TOKEN = "X-XSRF-Token";
    const X_REQUESTED_WITH = "X-Requested-With";
}
