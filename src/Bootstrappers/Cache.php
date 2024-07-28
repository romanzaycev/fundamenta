<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use DI\Container;
use DI\ContainerBuilder;
use Psr\Cache\CacheItemPoolInterface;
use Romanzaycev\Fundamenta\Components\Cache\ArrayAdapterFactory;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Contracts\Cache\CacheInterface;

class Cache extends ModuleBootstrapper
{
    public static function preconfigure(Configuration $configuration): void
    {
        $configuration->setDefaults(
            "cache",
            [
                "adapter_factory" => ArrayAdapterFactory::class,

                "options" => [
                    "array" => [
                        "default_lifetime" => 0,
                        "store_serialized" => false,
                        "max_lifetime" => 0,
                        "max_items" => 0,
                    ],
                ],
            ],
            [
                "adapter_factory",
            ],
        );
    }

    public static function boot(ContainerBuilder $builder, Configuration $configuration): void
    {
        $builder->addDefinitions([
            CacheItemPoolInterface::class => static function (Container $container) use ($configuration) {
                return $container
                    ->get($configuration->get("cache.adapter_factory"))
                    ->get($configuration);
            },

            CacheInterface::class => static function (Container $container) {
                return new Psr16Cache($container->get(CacheItemPoolInterface::class));
            },
        ]);
    }

    public static function requires(): array
    {
        return [
            Dotenv::class,
            Monolog::class,
            Dbal::class,
        ];
    }
}
