<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac;

interface RoleRepository
{
    /**
     * @return Role[]
     */
    public function getBySubject(Subject|string $subjectOrId): array;

    public function add(Subject|string $subjectOrId, Role|string $roleOrCode): void;

    public function remove(Subject|string $subjectOrId, Role|string $roleOrCode): void;
}
