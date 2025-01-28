<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth;

use Psr\Http\Message\ServerRequestInterface;
use Romanzaycev\Fundamenta\Components\Auth\Middlewares\AuthedContextMiddleware;

final class AuthHelper
{
    public static function getContext(ServerRequestInterface $request): ?Context
    {
        return $request->getAttribute(AuthedContextMiddleware::AUTH_CONTEXT_ATTRIBUTE);
    }

    public static function isAuthorized(ServerRequestInterface $request): bool
    {
        $ctx = self::getContext($request);

        return $ctx
            && !$ctx->isClosed()
            && $ctx->getToken()
            && $ctx->getToken()->expiresAt() > new \DateTimeImmutable()
        ;
    }

    public static function getToken(ServerRequestInterface $request): ?Token
    {
        if (self::isAuthorized($request)) {
            return self::getContext($request)->getToken();
        }

        return null;
    }
}
