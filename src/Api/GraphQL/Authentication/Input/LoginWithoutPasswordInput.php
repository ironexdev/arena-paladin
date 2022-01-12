<?php declare(strict_types=1);

namespace Paladin\Api\GraphQL\Authentication\Input;

use Paladin\Enum\TranslatorEnum;
use Symfony\Component\Validator\Constraints as Assert;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

#[Input]
class LoginWithoutPasswordInput
{
    #[Assert\NotBlank]
    #[Field]
    private string $authorizationToken;

    #[Assert\Type(
        type: "bool"
    )]
    #[Field]
    private bool $remember = false;

    /**
     * @return string
     */
    public function getAuthorizationToken(): string
    {
        return $this->authorizationToken;
    }

    /**
     * @param string $authorizationToken
     */
    public function setAuthorizationToken(string $authorizationToken): void
    {
        $this->authorizationToken = $authorizationToken;
    }

    /**
     * @return bool
     */
    public function getRemember(): bool
    {
        return $this->remember;
    }

    /**
     * @param bool $remember
     */
    public function setRemember(bool $remember): void
    {
        $this->remember = $remember;
    }
}
