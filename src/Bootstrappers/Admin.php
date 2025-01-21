<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use Romanzaycev\Fundamenta\Components\Admin\Security\HostGuardMiddleware;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;

class Admin extends ModuleBootstrapper
{
    public static function preconfigure(Configuration $configuration): void
    {
        $configuration->setDefaults(
            "admin",
            [
                "paths" => [
                    "ui_base_path" => "/panel",
                    "ui_api_base_path" => "/panel/api",
                ],
                "security" => [
                    "allowed_hosts" => [],
                ],
            ],
            [
                "paths",
                "paths.ui_base_path",
                "paths.ui_api_base_path",
                "security",
            ],
        );
    }

    public static function middlewares(): array
    {
        return [
            HostGuardMiddleware::class,
        ];
    }

    public static function requires(): array
    {
        return [
            Slim::class,
            Auth::class,
            Rbac::class,
        ];
    }
}
