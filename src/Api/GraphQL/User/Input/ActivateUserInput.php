<?php declare(strict_types=1);

namespace Paladin\Api\GraphQL\User\Input;

use Paladin\Enum\TranslatorEnum;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use Symfony\Component\Validator\Constraints as Assert;

#[Input]
class ActivateUserInput
{
    #[Assert\NotBlank]
    #[Field]
    private string $authorizationToken;

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
}
