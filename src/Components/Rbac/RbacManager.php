<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac;

use Romanzaycev\Fundamenta\Components\Auth\User;

interface RbacManager
{
    public function hasPermission(User $subject, string|Permission $permissionCode): bool;

    /**
     * @return Role[]
     */
    public function getRoles(User $subject): array;

    /**
     * @return Permission[]
     */
    public function getPermissions(User $subject): array;
}
