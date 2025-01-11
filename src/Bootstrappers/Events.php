<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use DI\ContainerBuilder;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use function DI\autowire;
use function DI\get;

class Events extends ModuleBootstrapper
{
    public static function boot(ContainerBuilder $builder, Configuration $configuration): void
    {
        $builder->addDefinitions([
            EventDispatcher::class => autowire(EventDispatcher::class),
            EventDispatcherInterface::class => get(EventDispatcher::class),
        ]);
    }
}
