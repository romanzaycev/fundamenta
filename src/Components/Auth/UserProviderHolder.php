<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth;

interface UserProviderHolder
{
    /**
     * @return UserProvider[]
     */
    public function getProviders(): array;
}
