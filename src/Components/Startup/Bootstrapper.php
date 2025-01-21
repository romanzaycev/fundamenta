<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Startup;

use DI\ContainerBuilder;
use Psr\Http\Server\MiddlewareInterface;
use Romanzaycev\Fundamenta\Components\Startup\Provisioning\ProvisionDecl;
use Romanzaycev\Fundamenta\Configuration;
use Slim\App;

/**
 * @method static void afterContainerBuilt(?mixed ...$args)
 * @method static ProvisionDecl[] provisioning(?mixed ...$args)
 * @method static void router(App $app, ?mixed ...$args)
 * @method static array<string|callable|MiddlewareInterface> middlewares(?mixed ...$args)
 * @method static void booted(?mixed ...$args)
 */
interface Bootstrapper
{
    public static function preconfigure(Configuration $configuration): void;

    public static function boot(ContainerBuilder $builder, Configuration $configuration): void;

    /**
     * @return array<class-string<Bootstrapper>>
     */
    public static function requires(): array;
}
