<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Startup;

use DI\ContainerBuilder;
use Psr\Http\Server\MiddlewareInterface;
use Romanzaycev\Fundamenta\Components\Startup\Provisioning\ProvisionDecl;
use Romanzaycev\Fundamenta\Configuration;
use Slim\App;

/**
 * @method static void afterContainerBuilt()
 * @method static ProvisionDecl[] provisioning()
 * @method static void router(App $app)
 * @method static array<string|callable|MiddlewareInterface> middlewares()
 * @method static void booted()
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
