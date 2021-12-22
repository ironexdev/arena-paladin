<?php declare(strict_types=1);

namespace Paladin\Core;

use Paladin\Model\Document\User;

class CurrentUserService
{
    /**
     * @param User|null $user
     * @param bool $securelyAuthenticated
     */
    public function __construct(private ?User $user = null, private bool $securelyAuthenticated = false)
    {

    }

    /**
     * Returns true if the "current" user is logged
     */
    public function isAuthenticated(): bool
    {
        return (bool)$this->user;
    }

    public function logout()
    {
        Session::destroy();
        Cookie::unsetToken();
    }

    public function isSecurelyAuthenticated(): bool
    {
        return $this->securelyAuthenticated;
    }

    /**
     * Returns an object representing the current logged user.
     * Can return null if the user is not logged.
     */
    public function getUser(): ?User
    {
        return $this->user;
    }
}
