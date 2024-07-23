<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use Aura\Sql\ExtendedPdo;
use DI\ContainerBuilder;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;

class Postgres extends ModuleBootstrapper
{
    public static function preconfigure(Configuration $configuration): void
    {
        $configuration->setDefaults("postgres", [
            "port" => 5432,
            "pdo_options" => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ]);
    }

    public static function boot(ContainerBuilder $builder, Configuration $configuration): void
    {
        $builder->addDefinitions([
            Postgres::class => static function () use ($configuration) {
                return new ExtendedPdo(
                    sprintf(
                        'pgsql:host=%s;port=%d;dbname=%s',
                        $_ENV["PG_HOST"],
                        $configuration->get("postgres.port"),
                        $_ENV["PG_DB"],
                    ),
                    $_ENV["PG_USER"],
                    $_ENV["PG_PASSWORD"],
                    $configuration->get("postgres.pdo_options"),
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
