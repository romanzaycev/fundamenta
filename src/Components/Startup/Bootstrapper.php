<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Startup;

use DI\ContainerBuilder;
use Romanzaycev\Fundamenta\Configuration;

/**
 * @method void afterContainerBuilt(mixed ...$args)
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
