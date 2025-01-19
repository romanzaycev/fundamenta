<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac;

interface RoleProvider
{
    /**
     * @return Role[]
     */
    public function create(): array;
}
