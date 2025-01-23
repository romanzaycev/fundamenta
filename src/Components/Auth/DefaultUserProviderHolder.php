<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth;

class DefaultUserProviderHolder implements UserProviderHolder
{
    /**
     * @var UserProvider[]
     */
    private array $providers = [];

    /**
     * @return UserProvider[]
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    public function register(UserProvider $provider): void
    {
        $this->providers[$provider::class] = $provider;
    }
}
