<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac\Impl\InMemory;

use Romanzaycev\Fundamenta\Components\Rbac\Permission;
use Romanzaycev\Fundamenta\Components\Rbac\PermissionRepository;
use Romanzaycev\Fundamenta\Components\Rbac\Role;

class InMemoryPermissionRepository implements PermissionRepository
{
    /**
     * @var array<string, Permission>
     */
    private array $permissionMap = [];

    /**
     * @param Permission[] $permissions
     * @param array<string, string[]> $rolePermissions
     */
    public function __construct(array $permissions, private readonly array $rolePermissions)
    {
        foreach ($permissions as $permission) {
            $this->permissionMap[$permission->getCode()] = $permission;
        }

        foreach ($rolePermissions as $roleCode => $rolePermissionArray) {
            foreach ($rolePermissionArray as $permissionCode) {
                if (!isset($this->permissionMap[$permissionCode])) {
                    throw new \InvalidArgumentException("Unknown permission: " . $permissionCode);
                }
            }
        }
    }

    public function getByRole(Role|string $roleOrCode): array
    {
        $roleCode = $roleOrCode instanceof Role ? $roleOrCode->getCode() : $roleOrCode;
        $result = [];

        if (isset($this->rolePermissions[$roleCode])) {
            foreach ($this->rolePermissions[$roleCode] as $permissionCode) {
                $result[] = $this->permissionMap[$permissionCode];
            }
        }

        return $result;
    }

    public function getByRoles(string|Role ...$roleOrCode): array
    {
        $result = [];
        $codes = array_map(
            static fn (string|Role $r): string => $r instanceof Role ? $r->getCode() : $r,
            $roleOrCode,
        );

        foreach ($codes as $roleCode) {
            if (isset($this->rolePermissions[$roleCode])) {
                foreach ($this->rolePermissions[$roleCode] as $permissionCode) {
                    $result[] = $this->permissionMap[$permissionCode];
                }
            }
        }

        return $result;
    }
}
