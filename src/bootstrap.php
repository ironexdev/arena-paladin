<?php declare(strict_types=1);

use Paladin\Enum\ResponseStatusCodeEnum;
use Psr\Log\LoggerInterface;
use Paladin\Enum\EnvironmentEnum;
use Paladin\Core\Kernel;
use DI\ContainerBuilder;

require_once(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "config.php");

if (ERROR_REPORTING === "true") {
    error_reporting(E_ALL);
    ini_set("display_errors", "On");
}

if (FORCE_HTTPS === "true") {
    if (!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] === "off") {
        echo "Website can only be accessed via HTTPS protocol";
        exit;
    }
}

const APP_DIRECTORY = __DIR__;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

date_default_timezone_set("UTC");

$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(true);
$containerBuilder->useAnnotations(true);
$containerBuilder->addDefinitions(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "config-di.php");
if (ENVIRONMENT === EnvironmentEnum::PRODUCTION) {
    // TODO test this on production
    $containerBuilder->enableCompilation(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "var" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "php-di");
}

$container = $containerBuilder->build();

try {
    $container->make(Kernel::class);
} catch (Throwable $e) {
    $errorCode = $e->getCode();
    $errorCode = // Set response code to 500, if Throwable's code is < 400 or > 599
        !$errorCode || $errorCode < ResponseStatusCodeEnum::BAD_REQUEST || $errorCode > 599
        ? ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR : $errorCode;

    $logger = $container->get(LoggerInterface::class);
    $logger->error($e->getMessage(), $e->getTrace());

    CORSHeaders();

    if (ENVIRONMENT === EnvironmentEnum::DEVELOPMENT) {
        if ($errorCode >= ResponseStatusCodeEnum::BAD_REQUEST && $errorCode < ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR) {
            errorResponse($errorCode, $e->getMessage());
        } else {
            http_response_code($errorCode);
            throw $e;
        }
    } else {
        if ($errorCode >= ResponseStatusCodeEnum::BAD_REQUEST && $errorCode < ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR) {
            errorResponse($errorCode, $e->getMessage());
        } else {
            errorResponse(ResponseStatusCodeEnum::INTERNAL_SERVER_ERROR);
        }
    }
}

function CORSHeaders()
{
    header("Access-Control-Allow-Credentials: " . ACCESS_CONTROL_ALLOW_CREDENTIALS);
    header("Access-Control-Allow-Origin: " . CLIENT_URL);
    header("Access-Control-Allow-Headers: " . ACCESS_CONTROL_ALLOW_HEADERS);
    header("Access-Control-Expose-Headers: " . ACCESS_CONTROL_EXPOSE_HEADERS);
}

function errorResponse(int $code, string $message = null)
{
    http_response_code($code);

    if ($message) {
        header("Content-Type: application/json");

        echo json_encode([
            "errors" => [
                "message" => $message
            ]
        ]);
    }
}
