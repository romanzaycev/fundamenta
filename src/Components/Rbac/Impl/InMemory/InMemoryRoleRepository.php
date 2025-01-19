<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac\Impl\InMemory;

use Romanzaycev\Fundamenta\Components\Auth\User;
use Romanzaycev\Fundamenta\Components\Rbac\Role;
use Romanzaycev\Fundamenta\Components\Rbac\RoleRepository;

class InMemoryRoleRepository implements RoleRepository
{
    /**
     * @var array<string, Role>
     */
    private array $roleMap = [];

    /**
     * @param Role[] $roles
     * @param array<string, string[]> $subjectRoles
     */
    public function __construct(array $roles, private array $subjectRoles)
    {
        foreach ($roles as $role) {
            $this->roleMap[$role->getCode()] = $role;
        }

        foreach ($subjectRoles as $subjectId => $subjectRolesArray) {
            foreach ($subjectRolesArray as $roleCode) {
                if (!isset($this->roleMap[$roleCode])) {
                    throw new \InvalidArgumentException("Unknown role: " . $roleCode);
                }
            }
        }
    }

    public function getBySubject(User|string $subjectOrId): array
    {
        $subjectId = $subjectOrId instanceof User ? $subjectOrId->getId() : $subjectOrId;
        $result = [];

        if (isset($this->subjectRoles[$subjectId])) {
            foreach ($this->subjectRoles[$subjectId] as $roleCode) {
                $result[] = $this->roleMap[$roleCode];
            }
        }

        return $result;
    }

    public function add(User|string $subjectOrId, Role|string $roleOrCode): void
    {
        $subjectId = $subjectOrId instanceof User ? $subjectOrId->getId() : $subjectOrId;
        $roleCode = $roleOrCode instanceof Role ? $roleOrCode->getCode() : $roleOrCode;

        if (isset($this->roleMap[$roleCode])) {
            if (!isset($this->subjectRoles[$subjectId])) {
                $this->subjectRoles[$subjectId] = [];
            }

            $this->subjectRoles[$subjectId][] = $roleCode;
        }
    }

    public function remove(User|string $subjectOrId, Role|string $roleOrCode): void
    {
        $subjectId = $subjectOrId instanceof User ? $subjectOrId->getId() : $subjectOrId;
        $roleCode = $roleOrCode instanceof Role ? $roleOrCode->getCode() : $roleOrCode;

        if (isset($this->subjectRoles[$subjectId])) {
            $key = array_search($roleCode, $this->subjectRoles[$subjectId], true);

            if ($key !== false) {
                unset($this->subjectRoles[$subjectId][$key]);

                if (empty($this->subjectRoles[$subjectId])) {
                    unset($this->subjectRoles[$subjectId]);
                }
            }
        }
    }
}
