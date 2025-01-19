<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac;

interface PermissionProvider
{
    /**
     * @return Permission[]
     */
    public function create(): array;
}
