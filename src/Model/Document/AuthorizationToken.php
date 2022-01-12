<?php declare(strict_types=1);

namespace Paladin\Model\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JetBrains\PhpStorm\Pure;
use Paladin\Exception\Client\InvalidAuthorizationTokenException;
use Paladin\Model\Document\DocumentTrait\ActiveTrait;
use Paladin\Model\Document\DocumentTrait\CreatedTrait;
use Paladin\Model\Document\DocumentTrait\UpdatedTrait;
use phpDocumentor\Reflection\Types\Self_;

/**
 * @ODM\Document(repositoryClass="Paladin\Model\Repository\AuthorizationToken\AuthorizationTokenRepository")
 * @ODM\HasLifecycleCallbacks
 */
class AuthorizationToken extends AbstractDocument
{
    use ActiveTrait;
    use CreatedTrait;
    use UpdatedTrait;

    /** @ODM\Field(type="string") */
    private string $action;

    /** @ODM\Field(type="string") */
    private string $selector;

    /** @ODM\Field(type="string") */
    private string $validator;

    /** @ODM\ReferenceOne(targetDocument=User::class) */
    private User $user;

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
     * Get AUTHORIZATION token selector and validator from string
     * @throws InvalidAuthorizationTokenException
     */
    public static function getSelectorAndValidatorFromString(string $authorizationTokenString): array
    {
        $parsedAuthorizationTokenString = explode(":", $authorizationTokenString);

        $selector = $parsedAuthorizationTokenString[0] ?? null;
        $validator = $parsedAuthorizationTokenString[1] ?? null;

        if (!$selector || !$validator) {
            throw new InvalidAuthorizationTokenException("Invalid Authorization Token string format");
        }

        return [
            $selector,
            $validator
        ];
    }

    #[Pure] public function __toString(): string
    {
        return $this->getSelector() . ":" . $this->getValidator();
    }
}
