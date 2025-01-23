<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Romanzaycev\Fundamenta\Components\Auth\AuthHelper;
use Slim\Exception\HttpUnauthorizedException;

class AuthGuardMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authContext = AuthHelper::getContext($request);

        if (!$authContext || !$authContext->getToken()) {
            throw new HttpUnauthorizedException($request);
        }

        return $handler->handle($request);
    }
}
