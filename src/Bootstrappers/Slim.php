<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use Romanzaycev\Fundamenta\Components\Server\Slim\HttpErrorHandler;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;

class Slim extends ModuleBootstrapper
{
    public static function preconfigure(Configuration $configuration): void
    {
        $configuration->setDefaults(
            "slim",
            [
                "error_handler" => HttpErrorHandler::class,
                "error_middleware" => [
                    "display_error_details" => false,
                    "log_errors" => false,
                    "log_error_details" => false,
                ],
                "middlewares" => [],
            ],
            [
                "error_handler",

                "error_middleware",
                "error_middleware.display_error_details",
                "error_middleware.log_errors",
                "error_middleware.log_error_details",
            ]
        );
    }

    public static function requires(): array
    {
        return [
            Dotenv::class,
            Monolog::class,
            OpenSwoole::class,
        ];
    }
}
