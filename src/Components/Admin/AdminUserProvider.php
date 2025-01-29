<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Admin;

use Romanzaycev\Fundamenta\Components\Auth\UserProvider;

interface AdminUserProvider extends UserProvider
{
    public function getByLogin(string $login): ?AdminUser;

    /**
     * @return AdminUser[]
     */
    public function getList(): array;

    public function update(AdminUser $user): void;
}
