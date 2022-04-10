<?php declare(strict_types=1);

namespace Paladin\Api\GraphQL\Authorization\Input;

use Paladin\Enum\AuthorizationActionEnum;
use Paladin\Enum\TranslatorEnum;
use Symfony\Component\Validator\Constraints as Assert;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

#[Input]
class CreateAuthorizationTokenInput
{
    #[Assert\NotBlank]
    #[Assert\Email(
        message: TranslatorEnum::INVALID_EMAIL_FORMAT
    )]
    #[Field]
    private string $email;

    #[Assert\Choice([
        AuthorizationActionEnum::LOGIN_WITHOUT_PASSWORD,
        AuthorizationActionEnum::ACTIVATE_USER
    ])]
    #[Field]
    private string $action;

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }
}
