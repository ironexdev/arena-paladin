<?php declare(strict_types=1);

namespace Paladin\Api\GraphQL\Authentication\Output;

class LoginOutputFactory
{
    public function create(string $authenticationCode): LoginOutput
    {
        $loginOutput = new LoginOutput();

        $loginOutput->setAuthenticationCode($authenticationCode);

        return $loginOutput;
    }
}
