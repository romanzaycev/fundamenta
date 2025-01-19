<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use Romanzaycev\Fundamenta\Components\Rbac\PermissionHolder;
use Romanzaycev\Fundamenta\Components\Rbac\PermissionProvider;
use Romanzaycev\Fundamenta\Components\Rbac\RoleHolder;
use Romanzaycev\Fundamenta\Components\Rbac\RoleProvider;
use Romanzaycev\Fundamenta\Components\Startup\Provisioning\ProvisionDecl;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;

class Rbac extends ModuleBootstrapper
{
    public static function preconfigure(Configuration $configuration): void
    {
        $configuration->setDefaults(
            "rbac",
            [
                "schema" => [
                    "pg_schema" => "public",
                    "tables" => [
                        "role" => "rbac_roles",
                    ],
                ],
            ],
        );
    }

    public static function requires(): array
    {
        return [
            Dotenv::class,
        ];
    }

    /** @noinspection PhpParameterNameChangedDuringInheritanceInspection */
    public static function provisioning(RoleHolder $roleHolder, PermissionHolder $permissionHolder): array
    {
        return [
            new ProvisionDecl(
                RoleProvider::class,
                function (array $providers) use ($roleHolder) {
                    /** @var RoleProvider[] $providers */
                    foreach ($providers as $provider) {
                        foreach ($provider->create() as $role) {
                            $roleHolder->add($role);
                        }
                    }
                },
            ),

            new ProvisionDecl(
                PermissionProvider::class,
                function (array $providers) use ($permissionHolder) {
                    /** @var PermissionProvider[] $providers */
                    foreach ($providers as $provider) {
                        foreach ($provider->create() as $permission) {
                            $permissionHolder->add($permission);
                        }
                    }
                }
            )
        ];
    }
}
