<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use DI\Container;
use DI\ContainerBuilder;
use Romanzaycev\Fundamenta\Components\Admin\Bootstrapping\PermissionsProvider;
use Romanzaycev\Fundamenta\Components\Admin\Bootstrapping\RolesProvider;
use Romanzaycev\Fundamenta\Components\Admin\Bootstrapping\UiStaticHelper;
use Romanzaycev\Fundamenta\Components\Admin\Security\AdminBaseGuard;
use Romanzaycev\Fundamenta\Components\Rbac\Middlewares\PermissionGuardMiddleware;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use function DI\autowire;

class Admin extends ModuleBootstrapper
{
    public static function preconfigure(Configuration $configuration): void
    {
        $configuration->setDefaults(
            "admin",
            [
                "paths" => [
                    "ui_base_path" => "/panel",
                    "ui_api_base_path" => "/panel/api",
                ],
                "security" => [
                    "allowed_hosts" => [],
                ],
                "rbac" => [
                    RolesProvider::ADMINISTRATOR => [
                        PermissionsProvider::ADMIN_LOGIN,
                    ],
                    RolesProvider::EDITOR => [
                        PermissionsProvider::ADMIN_LOGIN,
                    ],
                ],
            ],
            [
                "paths",
                "paths.ui_base_path",
                "paths.ui_api_base_path",
                "security",
            ],
        );
    }

    public static function boot(ContainerBuilder $builder, Configuration $configuration): void
    {
        $builder->addDefinitions([
            RolesProvider::class => autowire(RolesProvider::class),
            PermissionsProvider::class => autowire(PermissionsProvider::class),
        ]);
    }

    /**
     * @throws \Throwable
     */
    public static function booted(Container $container): void
    {
        $resourcesDir = dirname(__DIR__, 2) . "/ui-public";
        $container
            ->make(UiStaticHelper::class, ["resourcesDir" => $resourcesDir])
            ->configure();
    }

    public static function middlewares(): array
    {
        return [
            AdminBaseGuard::class,
        ];
    }

    public static function router(
        App $app,
        Configuration $configuration,
        PermissionGuardMiddleware $permissionGuardMiddleware,
    ): void
    {
        $app
            ->group(
                $configuration->get("admin.paths.ui_api_base_path"),
                function (RouteCollectorProxy $proxy) {
                    // FIXME
                }
            )
            ->addMiddleware(
                $permissionGuardMiddleware->withPermission(PermissionsProvider::ADMIN_LOGIN),
            );
    }

    public static function requires(): array
    {
        return [
            Slim::class,
            Auth::class,
            Rbac::class,
            Events::class,
        ];
    }
}
