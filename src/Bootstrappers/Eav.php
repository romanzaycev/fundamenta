<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use DI\ContainerBuilder;
use Romanzaycev\Fundamenta\Components\Eav\Executor;
use Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql as PgsqlImpl;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\AttributeRepositoryInterface;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\EntityRepositoryInterface;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\SchemaInitializerInterface;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\ValueRepositoryInterface;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;
use function DI\autowire;

class Eav extends ModuleBootstrapper
{
    public static function preconfigure(Configuration $configuration): void
    {
        $configuration->setDefaults(
            "eav",
            [
                "schema" => [
                    "pg_schema" => "public",
                    "tables" => [
                        "entity" => "eav_entities",
                        "attribute" => "eav_attributes",
                        "value" => "eav_values",
                    ],
                ],
            ],
            [
                "schema",
                "schema.tables",
                "schema.tables.entity",
                "schema.tables.attribute",
                "schema.tables.value",
            ],
        );
    }

    public static function boot(ContainerBuilder $builder, Configuration $configuration): void
    {
        $builder->addDefinitions([
            SchemaInitializerInterface::class => autowire(PgsqlImpl\Repositories\PgsqlSchemaInitializer::class),
            EntityRepositoryInterface::class =>  autowire(PgsqlImpl\Repositories\PgsqlEntityRepository::class),
            AttributeRepositoryInterface::class => autowire(PgsqlImpl\Repositories\PgsqlAttributeRepository::class),
            ValueRepositoryInterface::class => autowire(PgsqlImpl\Repositories\PgsqlValueRepository::class),
            Executor::class => autowire(PgsqlImpl\PgsqlExecutor::class,)
        ]);
    }

    public static function requires(): array
    {
        return [
            Events::class,
            Dbal::class,
        ];
    }
}
