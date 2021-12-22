<?php declare(strict_types=1);

namespace Paladin\Core;

use Paladin\Enum\EnvironmentEnum;

class Session
{
    private static string $xsrfToken = "xsrf_token";
    private static string $expiration = "expiration";
    private static string $userId = "user_id";
    private static string $securelyAuthenticated = "securely_authenticated";

    public static function start()
    {
        if ($_ENV["ENVIRONMENT"] === EnvironmentEnum::DEVELOPMENT) {
            ini_set("session.cookie_secure", "Off");
        }

        session_start();
    }

    public static function destroy()
    {
        $params = session_get_cookie_params();
        setcookie(session_name(), "", -1,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );

        session_unset();
        session_destroy();
    }

    /**
     * @return array
     */
    public static function export(): array
    {
        return [
            "id" => session_id(),
            "data" => $_SESSION
        ];
    }

    public static function regenerate()
    {
        static::setExpiration(Utils::expiration(60));

        session_regenerate_id();

        static::unsetExpiration();
    }

    /**
     * @return string|null
     */
    public static function getXsrfToken(): ?string
    {
        return $_SESSION[static::$xsrfToken] ?? null;
    }

    /**
     * @param string $xsrfToken
     */
    public static function setXsrfToken(string $xsrfToken)
    {
        $_SESSION[static::$xsrfToken] = $xsrfToken;
    }

    /**
     * @return int|null
     */
    public static function getExpiration(): ?int
    {
        return $_SESSION[static::$expiration] ?? null;
    }

    /**
     * @param int $expiration
     */
    public static function setExpiration(int $expiration): void
    {
        $_SESSION["expiration"] = $expiration;
    }

    public static function unsetExpiration()
    {
        unset($_SESSION[static::$expiration]);
    }

    /**
     * @return bool
     */
    public static function getSecurelyAuthenticated(): bool
    {
        return $_SESSION[static::$securelyAuthenticated] ?? false;
    }

    /**
     * @param bool $secure
     */
    public static function setSecurelyAuthenticated(bool $secure)
    {
        $_SESSION[static::$securelyAuthenticated] = $secure;
    }

    /**
     * @return string|null
     */
    public static function getUserId(): ?string
    {
        return $_SESSION[static::$userId] ?? null;
    }

    /**
     * @param string $userId
     */
    public static function setUserId(string $userId): void
    {
        $_SESSION[static::$userId] = $userId;
    }
}
