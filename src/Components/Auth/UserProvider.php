<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth;

interface UserProvider
{
    public function getUser(Token $token): ?User;
}
