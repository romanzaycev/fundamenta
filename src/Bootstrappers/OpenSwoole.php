<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use DI\Container;
use DI\ContainerBuilder;
use Psr\Log\LoggerInterface;
use Romanzaycev\Fundamenta\Components\Configuration\Env;
use Romanzaycev\Fundamenta\Components\Http\Static\StaticHandler;
use Romanzaycev\Fundamenta\Components\Server\OpenSwoole\ServerFactory;
use Romanzaycev\Fundamenta\Components\Server\OpenSwoole\SwooleStaticHandler;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;
use function DI\autowire;
use function DI\get;

class OpenSwoole extends ModuleBootstrapper
{
    public static function preconfigure(Configuration $configuration): void
    {
        $configuration->setDefaults(
            "openswoole",
            [
                "host" => "0.0.0.0",
                "port" => "8888",
                "mode" => SWOOLE_PROCESS,
                "settings" => [
                    "worker_num" => 2,
                    "document_root" => Env::getString("DOCUMENT_ROOT", ""),
                    "enable_static_handler" => true,
                    "max_request" => 0,
                    "http_index_files" => [
                        "index.html",
                        "index.htm",
                    ],
                ],
                "misc" => [
                    "ignore_favicon" => true,
                ],
            ],
            [
                "host",
                "port",
                "mode",

                "settings",
                "settings.worker_num",
                "settings.document_root",
                "settings.enable_static_handler",
                "settings.http_index_files",
            ],
        );
    }

    public static function boot(ContainerBuilder $builder, Configuration $configuration): void
    {
        $builder->addDefinitions([
            ServerFactory::class => static function (Container $container) use ($configuration) {
                return new ServerFactory(
                    $configuration->get("openswoole"),
                    $container->get(LoggerInterface::class),
                );
            },
            StaticHandler::class => autowire(SwooleStaticHandler::class),
            SwooleStaticHandler::class => get(StaticHandler::class),
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
