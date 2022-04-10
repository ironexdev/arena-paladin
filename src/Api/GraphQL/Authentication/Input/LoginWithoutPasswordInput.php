<?php declare(strict_types=1);

namespace Paladin\Api\GraphQL\Authentication\Input;

use Symfony\Component\Validator\Constraints as Assert;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

#[Input]
class LoginWithoutPasswordInput
{
    #[Assert\NotBlank]
    #[Field]
    private string $authorizationCode;

    /**
     * @return string
     */
    public function getAuthorizationCode(): string
    {
        return $this->authorizationCode;
    }

    /**
     * @param string $authorizationCode
     */
    public function setAuthorizationCode(string $authorizationCode): void
    {
        $this->authorizationCode = $authorizationCode;
    }
}
