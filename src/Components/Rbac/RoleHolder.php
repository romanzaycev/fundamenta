<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac;

final class RoleHolder
{
    /**
     * @var array<string, Role>
     */
    private array $roles = [];

    /**
     * @return Role[]
     */
    public function getRoles(): array
    {
        return array_values($this->roles);
    }

    public function add(Role $role): void
    {
        $code = $role->getCode();

        if (!isset($this->roles[$code])) {
            $this->roles[$code] = $role;
        }
    }
    public function get(string $code): ?Role
    {
        return $this->roles[$code] ?? null;
    }
}
