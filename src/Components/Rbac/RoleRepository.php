<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac;

use Romanzaycev\Fundamenta\Components\Auth\User;

interface RoleRepository
{
    /**
     * @return Role[]
     */
    public function getBySubject(User|string $subjectOrId): array;

    public function add(User|string $subjectOrId, Role|string $roleOrCode): void;

    public function remove(User|string $subjectOrId, Role|string $roleOrCode): void;
}
