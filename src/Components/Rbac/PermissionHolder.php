<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac;

final class PermissionHolder
{
    /**
     * @var array<string, Permission>
     */
    private array $permissions = [];

    /**
     * @return Permission[]
     */
    public function getPermissions(): array
    {
        return array_values($this->permissions);
    }

    public function add(Permission $permission): void
    {
        $code = $permission->getCode();

        if (!isset($this->permissions[$code])) {
            $this->permissions[$code] = $permission;
        }
    }
    public function get(string $code): ?Permission
    {
        return $this->permissions[$code] ?? null;
    }
}
