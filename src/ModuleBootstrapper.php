<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta;

use DI\ContainerBuilder;
use Psr\Http\Server\MiddlewareInterface;
use Romanzaycev\Fundamenta\Components\Startup\Bootstrapper;
use Romanzaycev\Fundamenta\Components\Startup\HookManager;
use Romanzaycev\Fundamenta\Components\Startup\Provisioning\ProvisionDecl;
use Slim\App;

/**
 * @method static void afterContainerBuilt(?mixed ...$args)
 * @method static ProvisionDecl[] provisioning(?mixed ...$args)
 * @method static void router(App $app, ?mixed ...$args)
 * @method static array<string|callable|MiddlewareInterface> middlewares(?mixed ...$args)
 * @method static void booted(?mixed ...$args)
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
