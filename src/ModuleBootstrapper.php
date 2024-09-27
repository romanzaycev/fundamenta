<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta;

use DI\ContainerBuilder;
use Romanzaycev\Fundamenta\Components\Startup\Bootstrapper;
use Romanzaycev\Fundamenta\Components\Startup\HookManager;

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
