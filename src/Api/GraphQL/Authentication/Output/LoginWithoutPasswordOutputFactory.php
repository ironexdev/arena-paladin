<?php declare(strict_types=1);

namespace Paladin\Api\GraphQL\Authentication\Output;

class LoginWithoutPasswordOutputFactory
{
    public function create(string $authenticationCode): LoginWithoutPasswordOutput
    {
        $loginOutput = new LoginWithoutPasswordOutput;

        $loginOutput->setAuthenticationCode($authenticationCode);

        return $loginOutput;
    }
}
