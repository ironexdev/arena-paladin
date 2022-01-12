<?php declare(strict_types=1);

namespace Paladin\Model\Entity;

use JetBrains\PhpStorm\Pure;
use Paladin\Exception\Client\InvalidAuthenticationTokenException;

class AuthenticationToken
{
    private string $selector;

    private string $validator;

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
     * Get AUTHENTICATION token selector and validator from string
     * @throws InvalidAuthenticationTokenException
     */
    public static function getSelectorAndValidatorFromString(string $authenticationTokenString): array
    {
        $parsedAuthenticationTokenString = explode(":", $authenticationTokenString);

        $selector = $parsedAuthenticationTokenString[0] ?? null;
        $validator = $parsedAuthenticationTokenString[1] ?? null;

        if (!$selector || !$validator) {
            throw new InvalidAuthenticationTokenException("Invalid Authentication Token string format");
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
