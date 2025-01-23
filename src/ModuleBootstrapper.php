<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta;

use DI\ContainerBuilder;
use Psr\Http\Server\MiddlewareInterface;
use Romanzaycev\Fundamenta\Components\Startup\Bootstrapper;
use Romanzaycev\Fundamenta\Components\Startup\HookManager;
use Romanzaycev\Fundamenta\Components\Startup\Provisioning\ProvisionDecl;
use Slim\App;

/**
 * @method static void afterContainerBuilt()
 * @method static ProvisionDecl[] provisioning()
 * @method static void router(App $app)
 * @method static array<string|callable|MiddlewareInterface> middlewares()
 * @method static void booted()
 */
abstract class ModuleBootstrapper implements Bootstrapper
{
    private function __construct()
    {
        // Constructor is prohibited
    }

    public static function preconfigure(Configuration $configuration): void {}

    public static function boot(ContainerBuilder $builder, Configuration $configuration): void {}

    public static function hooks(HookManager $hookManager): void {}

    /**
     * @return array<class-string<Bootstrapper>>
     */
    public static function requires(): array
    {
        return [];
    }
}
