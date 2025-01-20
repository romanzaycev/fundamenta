<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use DI\Container;
use DI\ContainerBuilder;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Romanzaycev\Fundamenta\Components\Startup\Provisioning\ProvisionDecl;
use Romanzaycev\Fundamenta\Components\Views\EngineManager;
use Romanzaycev\Fundamenta\Components\Views\ViewEngineProvider;
use Romanzaycev\Fundamenta\Components\Views\ViewManager;
use Romanzaycev\Fundamenta\Components\Views\View;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;
use function DI\get;

class Views extends ModuleBootstrapper
{
    public static function preconfigure(Configuration $configuration): void
    {
        $configuration->setDefaults(
            "views",
            [
                "cache" => [
                    "enabled" => false,
                    "ttl_seconds" => 300,
                    "autokey" => "none", // template_name || template_name_and_data || none
                ],
            ],
            [
                "cache.enabled",
                "cache.ttl_seconds",
            ],
        );
    }

    public static function boot(ContainerBuilder $builder, Configuration $configuration): void
    {
        $builder->addDefinitions([
            ViewManager::class => static function (Container $container) use ($configuration) {
                return new ViewManager(
                    $configuration,
                    $container->get(CacheItemPoolInterface::class),
                    $container->get(LoggerInterface::class),
                );
            },
            EngineManager::class => get(ViewManager::class),
            View::class => get(ViewManager::class),
        ]);
    }

    public static function provisioning(EngineManager $engineManager): array
    {
        return [
            new ProvisionDecl(
                ViewEngineProvider::class,
                function (array $providers) use ($engineManager): void
                {
                    foreach ($providers as $provider) {
                        /** @var ViewEngineProvider $provider */
                        $provider->register($engineManager);
                    }
                }
            ),
        ];
    }

    public static function requires(): array
    {
        return [
            Dotenv::class,
            Monolog::class,
            Cache::class,
        ];
    }
}
