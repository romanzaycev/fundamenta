<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use DI\Container;
use DI\ContainerBuilder;
use Romanzaycev\Fundamenta\Components\Admin\Bootstrapping\AdminTokenStorage;
use Romanzaycev\Fundamenta\Components\Admin\Bootstrapping\PermissionsProvider;
use Romanzaycev\Fundamenta\Components\Admin\Bootstrapping\RolesProvider;
use Romanzaycev\Fundamenta\Components\Admin\Bootstrapping\Routing;
use Romanzaycev\Fundamenta\Components\Admin\Bootstrapping\UiStaticHelper;
use Romanzaycev\Fundamenta\Components\Admin\Providers\PgsqlUserProvider;
use Romanzaycev\Fundamenta\Components\Admin\Security\AdminBaseGuard;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;
use Slim\App;
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
                    "ui_api_base_path" => "/panelapi",
                ],
                "security" => [
                    "allowed_hosts" => [],
                    "auth" => [
                        "ttl" => "PT24H",
                    ],
                ],
                "rbac" => [
                    RolesProvider::ADMINISTRATOR => [
                        PermissionsProvider::ADMIN_LOGIN,
                    ],
                    RolesProvider::EDITOR => [
                        PermissionsProvider::ADMIN_LOGIN,
                    ],
                ],
                "providers" => [
                    "user" => [
                        "pgsql" => [
                            "schema" => "public",
                            "table" => "admin_users",
                        ],
                    ]
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
            Routing::class => autowire(Routing::class),
            AdminTokenStorage::class => autowire(AdminTokenStorage::class),
            RolesProvider::class => autowire(RolesProvider::class),
            PermissionsProvider::class => autowire(PermissionsProvider::class),
            PgsqlUserProvider::class => autowire(PgsqlUserProvider::class),
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
        Routing $routing,
    ): void
    {
        $routing->configure($app);
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
