<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Admin\Internals\Providers;

use Romanzaycev\Fundamenta\Components\Rbac\Models\Role;
use Romanzaycev\Fundamenta\Components\Rbac\RoleProvider;

class RolesProvider implements RoleProvider
{
    public const ADMINISTRATOR = "fnda.admin.role_administrator";
    public const EDITOR = "fnda.admin.role_editor";

    public function create(): array
    {
        return [
            new Role(
                self::ADMINISTRATOR,
                "Administrator",
            ),
            new Role(
                self::EDITOR,
                "Editor",
            ),
        ];
    }
}
