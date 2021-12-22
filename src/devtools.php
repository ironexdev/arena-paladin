<?php declare(strict_types=1);

use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

// Pretty errors
$whoops = $container->get(Run::class);
$whoops->pushHandler($container->get(PrettyPageHandler::class));
$whoops->register();

// Pretty print
function pretty_print($value)
{
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Origin: " . $_ENV["CLIENT_URL"]);
    header("Access-Control-Allow-Headers: *");
    header("Content-Type: application/json");

    echo json_encode($value);
}
