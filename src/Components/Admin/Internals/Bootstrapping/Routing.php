<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Admin\Internals\Bootstrapping;

use Romanzaycev\Fundamenta\Components\Admin\Controllers\Auth;
use Romanzaycev\Fundamenta\Components\Admin\Internals\Providers\PermissionsProvider;
use Romanzaycev\Fundamenta\Components\Rbac\Middlewares\PermissionGuardMiddleware;
use Romanzaycev\Fundamenta\Configuration;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

class Routing
{
    private bool $isConfigured = false;

    public function __construct(
        private readonly Configuration $configuration,
        private readonly PermissionGuardMiddleware $permissionGuardMiddleware,
    ) {}

    public function configure(App $app): void
    {
        if ($this->isConfigured) {
            return;
        }

        $middleware = $this->permissionGuardMiddleware;
        $app
            ->group(
                $this->configuration->get("admin.paths.ui_api_base_path"),
                function (RouteCollectorProxy $proxy) use ($middleware) {
                    $proxy->post("/login", Auth::class . ":make");

                    $proxy
                        ->group(
                            "",
                            function (RouteCollectorProxy $protectedGroup) {
                                $protectedGroup->post("/refresh", Auth::class . ":refresh");
                            }
                        )
                        ->addMiddleware(
                            $middleware->withPermission(PermissionsProvider::ADMIN_LOGIN),
                        )
                    ;
                }
            );

        $this->isConfigured = true;
    }
}
