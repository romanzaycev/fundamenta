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

        return $ctx && $ctx->getToken() && !$ctx->isClosed();
    }
}
