<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Modules;

use DI\Container;
use DI\ContainerBuilder;
use Romanzaycev\Fundamenta\Bootstrapper;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\Helpers\ClassFinder;
use Romanzaycev\Fundamenta\Helpers\ClassFinderFilter;
use Romanzaycev\Fundamenta\Helpers\FrameworkClassFinder;
use Romanzaycev\Fundamenta\ModuleBootstrapper;

final class ModulesConfigurator
{
    private string $appPath;
    private string $appNamespace;
    private ?array $sorted = null;

    public function __construct(
        private readonly Configuration $configuration,
        private readonly ContainerBuilder $containerBuilder,
    )
    {
        $this->appPath = $this->configuration->get("app.path");
        $this->appNamespace = $this->configuration->get("app.namespace");
    }

    /**
     * @throws \Throwable
     */
    public function preconfigure(): void
    {
        foreach ($this->getSorted() as $bootstrapper) {
            call_user_func([$bootstrapper, "preconfigure"], $this->configuration);
        }
    }

    /**
     * @throws \Throwable
     */
    public function boot(): void
    {
        foreach ($this->getSorted() as $bootstrapper) {
            call_user_func([$bootstrapper, "boot"], $this->containerBuilder, $this->configuration);
        }
    }

    /**
     * @throws \Throwable
     */
    public function configureRouters(Container $container): void
    {
        foreach ($this->getSorted() as $bootstrapper) {
            if (method_exists($bootstrapper, "router")) {
                $container->call([$bootstrapper, "router"]);
            }
        }
    }

    /**
     * @return class-string[]
     * @throws \Throwable
     */
    private function getSorted(): array
    {
        if ($this->sorted) {
            return $this->sorted;
        }

        $this->sorted = [];

        foreach ($this->getClasses()->getTopologicalSorted() as $classString) {
            $this->sorted[] = $classString;
        }

        return $this->sorted;
    }

    /**
     * @throws \Throwable
     */
    private function getClasses(): DependencyGraph
    {
        $finderFilter = (new ClassFinderFilter())->withSubclassOf(Bootstrapper::class);

        $frameworkClassFinder = FrameworkClassFinder::create($this->configuration);
        $frameworkModules = $frameworkClassFinder->find($finderFilter);

        $applicationClassFinder = new ClassFinder(
            $this->appPath,
            $this->appNamespace,
        );
        $applicationModules = $applicationClassFinder->find($finderFilter);
        $classes = array_merge(
            $frameworkModules,
            $applicationModules,
        );
        $dg = new DependencyGraph();

        foreach ($classes as $class) {
            if ($class !== ModuleBootstrapper::class) {
                $dg->requires($class, call_user_func([$class, "requires"]));
            }
        }

        return $dg;
    }
}
