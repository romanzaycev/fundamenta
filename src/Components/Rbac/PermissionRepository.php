<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac;

interface PermissionRepository
{
    /**
     * @return Permission[]
     */
    public function getByRole(Role|string $roleOrCode): array;

    /**
     * @return Permission[]
     */
    public function getByRoles(Role|string ...$roleOrCode): array;
}
