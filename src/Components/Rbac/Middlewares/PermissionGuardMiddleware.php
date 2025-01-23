<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Romanzaycev\Fundamenta\Components\Auth\AuthHelper;
use Romanzaycev\Fundamenta\Components\Auth\AuthManager;
use Romanzaycev\Fundamenta\Components\Rbac\Models\Permission;
use Romanzaycev\Fundamenta\Components\Rbac\RbacManager;
use Slim\Exception\HttpForbiddenException;

class PermissionGuardMiddleware implements MiddlewareInterface
{
    private ?string $permissionCode = null;

    public function __construct(
        private readonly AuthManager $authManager,
        private readonly RbacManager $rbacManager,
    ) {}

    public function withPermission(Permission|string $permissionOrCode): self
    {
        $instance = clone $this;
        $instance->permissionCode = $permissionOrCode instanceof Permission
            ? $permissionOrCode->getCode()
            : $permissionOrCode;

        return $instance;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->permissionCode) {
            throw new \RuntimeException("Empty permission");
        }

        if (!AuthHelper::isAuthorized($request)) {
            throw new HttpForbiddenException($request);
        }

        $authContext = AuthHelper::getContext($request);
        $token = $authContext->getToken();

        if (!$token) {
            throw new HttpForbiddenException($request);
        }

        $user = $this->authManager->getUser($token);

        if (!$user) {
            throw new HttpForbiddenException($request);
        }

        if (!$this->rbacManager->hasPermission($user, $this->permissionCode)) {
            throw new HttpForbiddenException($request);
        }

        return $handler->handle($request);
    }
}
