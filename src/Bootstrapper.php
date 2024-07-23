<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta;

use DI\ContainerBuilder;

interface Bootstrapper
{
    public static function preconfigure(Configuration $configuration): void;

    public static function boot(ContainerBuilder $builder, Configuration $configuration): void;

    /**
     * @return array<class-string<Bootstrapper>>
     */
    public static function requires(): array;
}
