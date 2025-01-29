<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use DI\Container;
use DI\ContainerBuilder;
use Romanzaycev\Fundamenta\Components\Admin\Internals\Bootstrapping\ContainerConfigurator;
use Romanzaycev\Fundamenta\Components\Admin\Internals\Bootstrapping\Routing;
use Romanzaycev\Fundamenta\Components\Admin\Internals\Bootstrapping\UiStaticHelper;
use Romanzaycev\Fundamenta\Components\Admin\Internals\Providers\PermissionsProvider;
use Romanzaycev\Fundamenta\Components\Admin\Internals\Providers\PgsqlUserProvider;
use Romanzaycev\Fundamenta\Components\Admin\Internals\Providers\RolesProvider;
use Romanzaycev\Fundamenta\Components\Admin\Internals\Rbac\PermissionRepository;
use Romanzaycev\Fundamenta\Components\Admin\Internals\Security\AdminBaseGuard;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;
use Slim\App;

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
                        "hotp_required" => false,
                    ],
                ],

                "rbac" => [
                    "map" => [
                        RolesProvider::ADMINISTRATOR => [
                            PermissionsProvider::ADMIN_LOGIN,
                        ],
                        RolesProvider::EDITOR => [
                            PermissionsProvider::ADMIN_LOGIN,
                        ],
                    ],
                    "permission_repository" => [
                        "class" => PermissionRepository::class,
                    ],
                ],

                "providers" => [
                    "users" => [
                        "class" => PgsqlUserProvider::class,
                        "pgsql" => [
                            "schema" => "public",
                            "table" => "admin_users",
                        ],
                    ],
                    "roles" => [
                        "class" => RolesProvider::class,
                    ],
                    "permissions" => [
                        "class" => PermissionsProvider::class,
                    ],
                ],
            ],
            [
                "paths",
                "paths.ui_base_path",
                "paths.ui_api_base_path",

                "security",

                "rbac",
                "rbac.map",
                "rbac.permission_repository",
                "rbac.permission_repository.class",

                "providers",
                "providers.users",
                "providers.users.class",
                "providers.roles",
                "providers.roles.class",
                "providers.permissions",
                "providers.permissions.class",
            ],
        );
    }

    public static function boot(
        ContainerBuilder $builder,
        Configuration $configuration,
    ): void
    {
        ContainerConfigurator::configure($builder, $configuration);
    }

    /**
     * @throws \Throwable
     */
    public static function booted(Container $container, Configuration $configuration): void
    {
        if ($configuration->get("auth.enabled", false) === false) {
            throw new \RuntimeException(
                "System misconfiguration, Admin component wants `auth.enabled` === true",
            );
        }

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
