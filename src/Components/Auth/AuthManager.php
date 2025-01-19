<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth;

readonly class AuthManager
{
    public function __construct(
        private UserProviderHolder $userProviderHolder,
    ) {}

    public function getUser(Token $token): ?User
    {
        foreach ($this->userProviderHolder->getProviders() as $provider) {
            if ($user = $provider->getUser($token)) {
                return $user;
            }
        }

        return null;
    }
}
