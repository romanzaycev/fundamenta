<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Startup;

use DI\Container;
use DI\ContainerBuilder;
use Romanzaycev\Fundamenta\Configuration;

final class ModulesConfigurator
{
    private ?array $sorted = null;

    /**
     * @param class-string[] $bootstrappers
     */
    public function __construct(
        private readonly Configuration $configuration,
        private readonly ContainerBuilder $containerBuilder,
        private readonly array $bootstrappers,
    ) {}

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
    public function afterContainerBuilt(Container $container): void
    {
        foreach ($this->getSorted() as $bootstrapper) {
            if (method_exists($bootstrapper, "afterContainerBuilt")) {
                $container->call([$bootstrapper, "afterContainerBuilt"]);
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
        $classes = array_values(array_unique($this->bootstrappers));
        self::validateBootstrapperClasses($classes);
        $dependencyClasses = [];

        $visitor = function (array $classes) use (&$dependencyClasses, &$visitor) {
            foreach ($classes as $class) {
                $deps = call_user_func([$class, "requires"]);

                foreach ($deps as $r) {
                    $dependencyClasses[] = $r;
                }

                $visitor($deps);
            }
        };

        $visitor($classes);
        $dependencyClasses = array_values(array_unique($dependencyClasses));
        $classes = array_merge($classes, $dependencyClasses);
        self::validateBootstrapperClasses($classes);

        $dg = new DependencyGraph();

        foreach ($classes as $class) {
            $dg->requires($class, call_user_func([$class, "requires"]));
        }

        return $dg;
    }

    protected final static function validateBootstrapperClasses(array $classes): void
    {
        foreach ($classes as $class) {
            if (!is_subclass_of($class, Bootstrapper::class)) {
                throw new \RuntimeException(sprintf(
                    'Class "%s" not implements `Bootstrapper` interface',
                    $class,
                ));
            }
        }
    }
}
