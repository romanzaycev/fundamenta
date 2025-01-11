<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use Cycle\Database\Config as DbConfig;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseManager;
use DI\Container;
use DI\ContainerBuilder;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;

class Dbal extends ModuleBootstrapper
{
    public static function preconfigure(Configuration $configuration): void
    {
        // @see https://cycle-orm.dev/docs/intro-install/current/en#configuration
        $configuration->setDefaults("dbal", [
            "default" => "default",
            "databases" => [
                "default" => [
                    // "connection" => "pgsql",
                ],
            ],
        ]);
    }

    public static function boot(ContainerBuilder $builder, Configuration $configuration): void
    {
        $builder->addDefinitions([
            DatabaseManager::class => static function () use ($configuration) {
                return new DatabaseManager(
                    new DbConfig\DatabaseConfig($configuration->get("dbal")),
                );
            },
            DatabaseInterface::class => static function (Container $container) {
                return $container->get(DatabaseManager::class)->database();
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
