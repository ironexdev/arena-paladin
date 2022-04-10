<?php declare(strict_types=1);

namespace Paladin\Model\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JetBrains\PhpStorm\Pure;
use Paladin\Exception\Client\InvalidAuthenticationCodeException;
use Paladin\Model\Document\DocumentTrait\ActiveTrait;
use Paladin\Model\Document\DocumentTrait\CreatedTrait;
use Paladin\Model\Document\DocumentTrait\UpdatedTrait;

/**
 * @ODM\Document(repositoryClass="Paladin\Model\Repository\AuthenticationToken\AuthenticationTokenRepository")
 * @ODM\HasLifecycleCallbacks
 */
class AuthenticationToken extends AbstractDocument
{
    use ActiveTrait;
    use CreatedTrait;
    use UpdatedTrait;

    /** @ODM\Field(type="string") */
    private string $selector;

    /** @ODM\Field(type="string") */
    private string $validator;

    /** @ODM\ReferenceOne(targetDocument=User::class) */
    private User $user;

    /**
     * @return string
     */
    public function getSelector(): string
    {
        return $this->selector;
    }

    /**
     * @param string $selector
     */
    public function setSelector(string $selector): void
    {
        $this->selector = $selector;
    }

    /**
     * @return string
     */
    public function getValidator(): string
    {
        return $this->validator;
    }

    /**
     * @param string $validator
     */
    public function setValidator(string $validator): void
    {
        $this->validator = $validator;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * Get Authentication token selector and validator from string
     * @throws InvalidAuthenticationCodeException
     */
    public static function parseAuthenticationCode(string $authenticationCode): array
    {
        $parsedAuthenticationCode = explode(":", $authenticationCode);

        $selector = $parsedAuthenticationCode[0] ?? null;
        $validator = $parsedAuthenticationCode[1] ?? null;

        if (!$selector || !$validator) {
            throw new InvalidAuthenticationCodeException("Invalid Authentication Code format");
        }

        return [
            $selector,
            $validator
        ];
    }

    #[Pure] public function __toString(): string // AuthenticationCode = selector:validator
    {
        return $this->getSelector() . ":" . $this->getValidator();
    }
}
