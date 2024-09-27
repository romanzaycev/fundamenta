<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth;

use DI\Container;
use Psr\Http\Message\ServerRequestInterface;
use Romanzaycev\Fundamenta\Exceptions\Domain\EntityNotFoundException;

class TokenStorageHolder implements TokenStorageSource
{
    /** @var TokenStorage[] */
    private array $persistent = [];

    /** @var TokenStorage[] */
    private array $forRequest = [];

    /** @var TokenStorageProvider[] */
    private array $forRequestProviders = [];

    public function addPersistent(TokenStorage $storage): void
    {
        $this->persistent[$storage::class] = $storage;
    }

    public function addForRequest(ServerRequestInterface $request, Container $container): void
    {
        foreach ($this->forRequestProviders as $provider) {
            $storage = $provider->createForRequest($request, $container);
            $this->forRequest[$storage::class] = $storage;
        }
    }

    public function terminateAllForRequest(): void
    {
        $this->forRequest = [];
    }

    /**
     * @return TokenStorage[]
     */
    public function getStorages(): array
    {
        return array_merge($this->persistent, $this->forRequest);
    }

    public function getStorage(string $class): TokenStorage
    {
        return $this->getStorages()[$class] ?? throw new EntityNotFoundException("Not found token storage " . $class);
    }

    public function registerForRequestProvider(TokenStorageProvider $provider): void
    {
        $this->forRequestProviders[] = $provider;
    }
}
