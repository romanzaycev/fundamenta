<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac\Internals;

use Romanzaycev\Fundamenta\Components\Auth\User;
use Romanzaycev\Fundamenta\Components\Rbac\Permission;
use Romanzaycev\Fundamenta\Components\Rbac\PermissionRepository;
use Romanzaycev\Fundamenta\Components\Rbac\RbacManager;
use Romanzaycev\Fundamenta\Components\Rbac\Role;
use Romanzaycev\Fundamenta\Components\Rbac\RoleRepository;

class GenericRbacManager implements RbacManager
{
    /**
     * @var RoleRepository[]
     */
    private array $roleRepositories = [];

    /**
     * @var PermissionRepository[]
     */
    private array $permissionRepositories = [];

    public function hasPermission(User $subject, string|Permission $permissionCode): bool
    {
        $permissionCode = $permissionCode instanceof Permission
            ? $permissionCode->getCode()
            : $permissionCode;

        foreach ($this->getRoles($subject) as $role) {
            foreach ($this->permissionRepositories as $permissionRepository) {
                $permissions = $permissionRepository->getByRole($role);

                foreach ($permissions as $permission) {
                    if ($permission->getCode() === $permissionCode) {
                        return true;
                    }
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
        $result = [];

        foreach ($this->roleRepositories as $roleRepository) {
            foreach ($roleRepository->getBySubject($subject) as $role) {
                if (!in_array($role, $result)) {
                    $result[] = $role;
                }
            }
        }

        return $result;
    }

    /**
     * @return Permission[]
     */
    public function getPermissions(User $subject): array
    {
        $result = [];

        foreach ($this->permissionRepositories as $permissionRepository) {
            foreach ($permissionRepository->getByRoles(...$this->getRoles($subject)) as $permission) {
                if (!in_array($permission, $result)) {
                    $result[] = $permission;
                }
            }
        }

        return $result;
    }

    public function add(PermissionRepository|RoleRepository $repository): void
    {
        if ($repository instanceof PermissionRepository) {
            if (!in_array($repository, $this->permissionRepositories)) {
                $this->permissionRepositories[] = $repository;
            }
        } else {
            if (!in_array($repository, $this->roleRepositories)) {
                $this->roleRepositories[] = $repository;
            }
        }
    }
}
