<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Startup;

use DI\ContainerBuilder;
use Romanzaycev\Fundamenta\Components\Startup\Provisioning\ProvisionDecl;
use Romanzaycev\Fundamenta\Configuration;
use Slim\App;

/**
 * @method void afterContainerBuilt(?mixed ...$args)
 * @method ProvisionDecl[] provisioning(?mixed ...$args)
 * @method void router(App $app, ?mixed ...$args)
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
