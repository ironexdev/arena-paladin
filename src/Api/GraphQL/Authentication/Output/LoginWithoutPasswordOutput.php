<?php declare(strict_types=1);

namespace Paladin\Api\GraphQL\Authentication\Output;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
class LoginWithoutPasswordOutput
{
    #[Field]
    private string $authenticationCode;
    /**
     * @return string
     */
    public function getAuthenticationCode(): string
    {
        return $this->authenticationCode;
    }

    /**
     * @param string $authenticationCode
     */
    public function setAuthenticationCode(string $authenticationCode): void
    {
        $this->authenticationCode = $authenticationCode;
    }
}
