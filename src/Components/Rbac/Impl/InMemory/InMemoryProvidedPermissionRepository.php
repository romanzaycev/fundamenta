<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac\Impl\InMemory;

use Romanzaycev\Fundamenta\Components\Rbac\PermissionHolder;

class InMemoryProvidedPermissionRepository extends InMemoryPermissionRepository
{
    public function __construct(PermissionHolder $permissionHolder, array $rolePermissions)
    {
        parent::__construct(
            $permissionHolder->getPermissions(),
            $rolePermissions,
        );
    }
}
