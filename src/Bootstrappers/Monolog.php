<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use DI\Container;
use DI\ContainerBuilder;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Romanzaycev\Fundamenta\Components\Configuration\Env;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;

class Monolog extends ModuleBootstrapper
{
    public static function preconfigure(Configuration $configuration): void
    {
        $configuration->setDefaults("monolog", [
            "name" => "",
            "stream" => 'php://stdout',
            "level" => LogLevel::NOTICE,
            "debug_level" => LogLevel::DEBUG,
            "file_log" => [
                "enabled" => false,
                "path" => "/tmp/file_log.log",
                "level" => LogLevel::ERROR,
            ],
        ]);
    }

    public static function boot(ContainerBuilder $builder, Configuration $configuration): void
    {
        $builder->addDefinitions([
            LoggerInterface::class => static function (Container $container) use ($configuration) {
                $logger = new Logger($configuration->get("monolog.name", ""));

                $formatter = new LineFormatter();
                $formatter->setMaxNormalizeDepth(20);
                $formatter->setMaxNormalizeItemCount(10000);

                $defaultHandler = new StreamHandler(
                    $configuration->get("monolog.stream", 'php://stdout'),
                    Env::getBool("IS_DEBUG", false)
                        ? $configuration->get("monolog.debug_level", LogLevel::DEBUG)
                        : $configuration->get("monolog.level", LogLevel::NOTICE),
                );
                $defaultHandler->setFormatter($formatter);
                $logger->pushHandler($defaultHandler);

                if ($configuration->get("monolog.file_log.enabled", false)) {
                    $errorHandler = new StreamHandler(
                        $configuration->get("monolog.file_log.path"),
                        $configuration->get("monolog.file_log.level", LogLevel::ERROR),
                    );
                    $logger->pushHandler($errorHandler);
                }

                return $logger;
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
