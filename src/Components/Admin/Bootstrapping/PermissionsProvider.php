<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Admin\Bootstrapping;

use Romanzaycev\Fundamenta\Components\Rbac\Models\Permission;
use Romanzaycev\Fundamenta\Components\Rbac\PermissionProvider;

class PermissionsProvider implements PermissionProvider
{
    public const ADMIN_LOGIN = "fnda.admin.perm_login";

    public function create(): array
    {
        return [
            new Permission(
                self::ADMIN_LOGIN,
                "Admin panel login",
            ),
        ];
    }
}
