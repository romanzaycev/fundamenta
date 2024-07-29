<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Extensions\Tooolooop;

use DI\Container;
use DI\ContainerBuilder;
use Romanzaycev\Fundamenta\Bootstrappers\Views;
use Romanzaycev\Fundamenta\Components\Configuration\Env;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\Exceptions\Domain\InvalidParamsException;
use Romanzaycev\Fundamenta\ModuleBootstrapper;
use Romanzaycev\Tooolooop\Engine;
use Romanzaycev\Tooolooop\Scope\Scope;

class Bootstrapper extends ModuleBootstrapper
{
    public static function preconfigure(Configuration $configuration): void
    {
        $configuration->setDefaults(
            "tooolooop",
            [
                "scope_class" => Scope::class,
                "directory" => Env::getString("VIEWS_PATH", ""),
                "extension" => "t.php",
            ],
            [
                "directory",
                "scope_class",
                "extension",
            ]
        );
    }

    /**
     * @throws InvalidParamsException
     */
    public static function boot(ContainerBuilder $builder, Configuration $configuration): void
    {
        if ($configuration->get("tooolooop.directory", "") === "") {
            throw new InvalidParamsException();
        }

        $builder->addDefinitions([
            TooolooopViewEngine::class => static function (Container $container) use ($configuration) {
                $tooolooop = new Engine(
                    directory: $configuration->get("tooolooop.directory")
                );
                $tooolooop->setContainer($container);
                $tooolooop->setContainerFetchingMethod("make");

                return new TooolooopViewEngine($tooolooop, $configuration);
            },
        ]);
    }

    public static function requires(): array
    {
        return [
            Views::class,
        ];
    }
}
