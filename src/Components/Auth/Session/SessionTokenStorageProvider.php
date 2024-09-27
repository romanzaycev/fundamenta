<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth\Session;

use DI\Container;
use Psr\Http\Message\ServerRequestInterface;
use Romanzaycev\Fundamenta\Components\Auth\Lifecycle;
use Romanzaycev\Fundamenta\Components\Auth\TokenStorage;
use Romanzaycev\Fundamenta\Components\Auth\TokenStorageProvider;

class SessionTokenStorageProvider implements TokenStorageProvider
{
    public function createPersistent(Container $container): TokenStorage
    {
        throw new \RuntimeException("Not applicable");
    }

    public function createForRequest(ServerRequestInterface $request, Container $container): TokenStorage
    {
        return new SessionTokenStorage($request);
    }

    public function getLifecycle(): Lifecycle
    {
        return Lifecycle::PER_REQUEST;
    }
}
