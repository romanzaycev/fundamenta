<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use DI\ContainerBuilder;
use Romanzaycev\Fundamenta\Components\Rbac\Internals\GenericRbacManager;
use Romanzaycev\Fundamenta\Components\Rbac\Middlewares\PermissionGuardMiddleware;
use Romanzaycev\Fundamenta\Components\Rbac\PermissionHolder;
use Romanzaycev\Fundamenta\Components\Rbac\PermissionProvider;
use Romanzaycev\Fundamenta\Components\Rbac\PermissionRepository;
use Romanzaycev\Fundamenta\Components\Rbac\RbacManager;
use Romanzaycev\Fundamenta\Components\Rbac\RoleHolder;
use Romanzaycev\Fundamenta\Components\Rbac\RoleProvider;
use Romanzaycev\Fundamenta\Components\Rbac\RoleRepository;
use Romanzaycev\Fundamenta\Components\Startup\Provisioning\ProvisionDecl;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;
use function DI\autowire;
use function DI\get;

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

    public static function boot(ContainerBuilder $builder, Configuration $configuration): void
    {
        $builder->addDefinitions([
            RbacManager::class => autowire(GenericRbacManager::class),
            GenericRbacManager::class => get(RbacManager::class),
            PermissionGuardMiddleware::class => autowire(PermissionGuardMiddleware::class),
        ]);
    }

    public static function requires(): array
    {
        return [
            Auth::class,
        ];
    }

    public static function provisioning(
        RoleHolder $roleHolder,
        PermissionHolder $permissionHolder,
        GenericRbacManager $rbacManager,
    ): array
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
            ),

            new ProvisionDecl(
                PermissionRepository::class,
                function (array $repositories) use ($rbacManager) {
                    foreach ($repositories as $repository) {
                        $rbacManager->add($repository);
                    }
                },
            ),

            new ProvisionDecl(
                RoleRepository::class,
                function (array $repositories) use ($rbacManager) {
                    foreach ($repositories as $repository) {
                        $rbacManager->add($repository);
                    }
                },
            ),
        ];
    }
}
