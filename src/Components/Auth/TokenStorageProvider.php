<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth;

use DI\Container;
use Psr\Http\Message\ServerRequestInterface;

interface TokenStorageProvider
{
    public function createPersistent(Container $container): TokenStorage;
    public function createForRequest(ServerRequestInterface $request, Container $container): TokenStorage;
    public function getLifecycle(): Lifecycle;
}
