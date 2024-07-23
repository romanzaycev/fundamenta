<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;

class Application extends ModuleBootstrapper
{
    public static function preconfigure(Configuration $configuration): void
    {
        $configuration->setDefaults("app", [], [
            "path",
            "namespace",
        ]);
    }

    public static function requires(): array
    {
        return [
            Dotenv::class,
            Monolog::class,
        ];
    }
}
