<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac;

use Romanzaycev\Fundamenta\Components\Auth\User;

readonly class RbacManager
{
    public function __construct(
        private RoleRepository $roleRepository,
        private PermissionRepository $permissionRepository,
    ) {}

    public function hasPermission(User $subject, string|Permission $permissionCode): bool
    {
        $permissionCode = $permissionCode instanceof Permission
            ? $permissionCode->getCode()
            : $permissionCode;

        foreach ($this->getRoles($subject) as $role) {
            $permissions = $this
                ->permissionRepository
                ->getByRole($role)
            ;

            foreach ($permissions as $permission) {
                if ($permission->getCode() === $permissionCode) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return Role[]
     */
    public function getRoles(User $subject): array
    {
        return $this
            ->roleRepository
            ->getBySubject($subject)
        ;
    }

    /**
     * @return Permission[]
     */
    public function getPermissions(User $subject): array
    {
        return $this
            ->permissionRepository
            ->getByRoles(
                ...$this->getRoles($subject)
            )
        ;
    }
}
