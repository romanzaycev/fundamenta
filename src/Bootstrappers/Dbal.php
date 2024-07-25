<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\DatabaseManager;
use DI\ContainerBuilder;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;

class Dbal extends ModuleBootstrapper
{
    public static function preconfigure(Configuration $configuration): void
    {
        $configuration->setDefaults("dbal", [
            "default" => "default",
            "databases" => [
                "default" => [],
            ]
        ]);
    }

    public static function boot(ContainerBuilder $builder, Configuration $configuration): void
    {
        $builder->addDefinitions([
            DatabaseManager::class => static function () use ($configuration) {
                new DatabaseManager(
                    new DatabaseConfig($configuration->get("dbal")),
                );
            },
        ]);
    }

    public static function requires(): array
    {
        return [
            Dotenv::class,
        ];
    }
}
