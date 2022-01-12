<?php declare(strict_types=1);

namespace Paladin\Api\GraphQL\Authentication\Input;

use Paladin\Enum\TranslatorEnum;
use Symfony\Component\Validator\Constraints as Assert;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

#[Input]
class LoginInput
{
    #[Assert\NotBlank]
    #[Assert\Email(
        message: TranslatorEnum::INVALID_EMAIL_FORMAT
    )]
    #[Field]
    private string $email;

    #[Assert\NotBlank]
    #[Assert\Length(
        max: 128,
        maxMessage: TranslatorEnum::STRING_MAX_LENGTH
    )]
    #[Field]
    private string $password;

    #[Assert\Type(
        type: "bool"
    )]
    #[Field]
    private bool $remember = false;

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
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
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
