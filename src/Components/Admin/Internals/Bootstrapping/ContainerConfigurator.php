<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Admin\Internals\Bootstrapping;

use DI\Container;
use DI\ContainerBuilder;
use Romanzaycev\Fundamenta\Components\Admin\AdminUserProvider;
use Romanzaycev\Fundamenta\Components\Admin\Internals\Auth\AdminTokenStorage;
use Romanzaycev\Fundamenta\Components\Admin\Internals\Rbac\PermissionRepository;
use Romanzaycev\Fundamenta\Components\Rbac\PermissionHolder;
use Romanzaycev\Fundamenta\Components\Rbac\PermissionProvider;
use Romanzaycev\Fundamenta\Components\Rbac\RoleProvider;
use Romanzaycev\Fundamenta\Configuration;
use function DI\autowire;
use function DI\get;

class ContainerConfigurator
{
    protected static bool $isConfigured = false;

    public static function configure(ContainerBuilder $builder, Configuration $configuration): void
    {
        if (self::$isConfigured) {
            return;
        }

        /** @var class-string<AdminUserProvider> $userProviderClass */
        $userProviderClass = $configuration->get("admin.providers.users.class");
        /** @var class-string<RoleProvider> $rolesProviderClass */
        $rolesProviderClass = $configuration->get("admin.providers.roles.class");
        /** @var class-string<PermissionProvider> $permissionsProviderClass */
        $permissionsProviderClass = $configuration->get("admin.providers.permissions.class");

        $definitions = [
            Routing::class => autowire(Routing::class),
            AdminTokenStorage::class => autowire(AdminTokenStorage::class),
            AdminUserProvider::class => get($userProviderClass),
            $rolesProviderClass => autowire($rolesProviderClass),
            $permissionsProviderClass => autowire($permissionsProviderClass),
        ];

        /** @var class-string<\Romanzaycev\Fundamenta\Components\Rbac\PermissionRepository> $rbacPermissionRepositoryClass */
        $rbacPermissionRepositoryClass = $configuration->get("admin.rbac.permission_repository.class");

        if ($rbacPermissionRepositoryClass === PermissionRepository::class) {
            $definitions[$rbacPermissionRepositoryClass] = static function (Container $c) use ($configuration) {
                return new PermissionRepository(
                    $c->get(PermissionHolder::class),
                    $configuration->get("admin.rbac.map"),
                );
            };
        } else {
            $definitions[$rbacPermissionRepositoryClass] = autowire($rbacPermissionRepositoryClass);
        }

        $builder->addDefinitions($definitions);

        self::$isConfigured = true;
    }
}
