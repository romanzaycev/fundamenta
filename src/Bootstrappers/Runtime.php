<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;

class Runtime extends ModuleBootstrapper
{
    public static function preconfigure(Configuration $configuration): void
    {
        $configuration->setDefaults("runtime", [], [
            "path",
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
