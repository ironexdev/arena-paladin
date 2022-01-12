<?php declare(strict_types=1);

namespace Paladin\Service\Security;

interface SecurityServiceInterface
{
    /**
     * @param string $password
     * @param string|int|null $algo
     * @param array $options
     * @return string
     */
    public function passwordHash(string $password, string|int|null $algo = PASSWORD_DEFAULT, array $options = []): string;

    /**
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function passwordVerify(string $password, string $hash): bool;

    /**
     * @param string $algorithm
     * @param string $data
     * @param bool $binary
     * @return string
     */
    public function hash(string $algorithm, string $data, bool $binary = false): string;

    /**
     * @param string $knownString
     * @param string $userString
     * @return bool
     */
    public function hashEquals(string $knownString, string $userString): bool;

    /**
     * @param int $length
     * @return string
     */
    public function randomBytes(int $length): string;

    /**
     * @param string $string
     * @return string
     */
    public function bin2hex(string $string): string;
}
