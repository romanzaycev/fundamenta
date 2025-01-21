<?php declare(strict_types=1);

namespace Romanzaycev\Devsite;

use DI\ContainerBuilder;
use Romanzaycev\Fundamenta\Bootstrappers\Admin;
use Romanzaycev\Fundamenta\Bootstrappers\Auth;
use Romanzaycev\Fundamenta\Bootstrappers\Eav;
use Romanzaycev\Fundamenta\Bootstrappers\Slim;
use Romanzaycev\Fundamenta\Components\Auth\Session\SessionTokenStorageProvider;
use Romanzaycev\Fundamenta\Components\Auth\Transport\UniversalTransportProvider;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\Extensions\Tooolooop;
use Slim\Routing\RouteCollectorProxy;
use function DI\autowire;

class Site extends \Romanzaycev\Fundamenta\ModuleBootstrapper
{
    public static function boot(ContainerBuilder $builder, Configuration $configuration): void
    {
        $builder->addDefinitions([
            SessionTokenStorageProvider::class => autowire(SessionTokenStorageProvider::class),
            UniversalTransportProvider::class => autowire(UniversalTransportProvider::class),
        ]);
    }

    public static function router(\Slim\App $app): void
    {
        $app->get("/test", UseCases\Test\Controller::class . ":index");
        $app->group("/protected", function (RouteCollectorProxy $group) {
            $group->get("", UseCases\Protected\Controller::class . ":index");
            $group->post("", UseCases\Protected\Controller::class . ":login");
            $group->post("/logout", UseCases\Protected\Controller::class . ":logout");
        });
    }

    public static function requires(): array
    {
        return [
            Admin::class,
            Eav::class,
            Auth::class,
            Slim::class,
            Tooolooop\Bootstrapper::class,
        ];
    }
}
