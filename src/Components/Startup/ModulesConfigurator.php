<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Startup;

use DI\Container;
use DI\ContainerBuilder;
use Romanzaycev\Fundamenta\Components\Startup\Provisioning\ProvisionDecl;
use Romanzaycev\Fundamenta\Configuration;
use Slim\App;

class ModulesConfigurator
{
    private ?array $sorted = null;

    /**
     * @param class-string<Bootstrapper>[] $bootstrappers
     */
    public function __construct(
        protected readonly Configuration $configuration,
        protected readonly ContainerBuilder $containerBuilder,
        protected readonly array $bootstrappers,
        protected readonly DefaultModuleManager $moduleManager,
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
            $this->moduleManager->markAsEnabled($bootstrapper);
        }
    }

    /**
     * @throws \Throwable
     */
    public function afterContainerBuilt(Container $container): void
    {
        $classes = $this->getSorted();
        $hookManager = $container->get(HookManager::class);

        foreach ($classes as $bootstrapper) {
            $container->call([$bootstrapper, "hooks"], [$hookManager]);
        }

        foreach ($classes as $bootstrapper) {
            if (method_exists($bootstrapper, "afterContainerBuilt")) {
                $container->call([$bootstrapper, "afterContainerBuilt"]);
            }
        }

        /** @var array<class-string, ProvisionDecl[]> $providersMap */
        $providersMap = [];

        foreach ($classes as $bootstrapper) {
            if (method_exists($bootstrapper, "provisioning")) {
                /** @var ProvisionDecl[] $provisionDeclarations */
                $provisionDeclarations = $container->call([$bootstrapper, "provisioning"]);

                if (!empty($provisionDeclarations)) {
                    foreach ($provisionDeclarations as $decl) {
                        $providerInterfaceClass = $decl->providerInterfaceClass;
                        $providersMap[$providerInterfaceClass] = $providersMap[$providerInterfaceClass] ?? [];
                        $providersMap[$providerInterfaceClass][] = $decl;
                    }
                }
            }
        }

        if (!empty($providersMap)) {
            $providers = array_keys($providersMap);
            $containerEntries = $container->getKnownEntryNames();

            foreach ($providers as $providerInterfaceClass) {
                $impls = [];

                foreach ($containerEntries as $containerEntry) {
                    if (is_subclass_of($containerEntry, $providerInterfaceClass)) {
                        $impls[] = $container->get($containerEntry);
                    }
                }

                if (!empty($impls)) {
                    foreach ($providersMap[$providerInterfaceClass] as $decl) {
                        /** @var ProvisionDecl $decl */
                        call_user_func($decl->acceptor, $impls);
                    }
                }
            }
        }

        $app = $container->get(App::class);
        $bootMiddlewares = [];

        foreach ($classes as $bootstrapper) {
            if (method_exists($bootstrapper, "middlewares")) {
                $md = $container->call([$bootstrapper, "middlewares"]);

                if (!empty($md)) {
                    foreach (array_reverse($md) as $m) {
                        $bootMiddlewares[] = $m;
                    }
                }
            }
        }

        if (!empty($bootMiddlewares)) {
            foreach (array_reverse($bootMiddlewares) as $middleware) {
                $app->add($middleware);
            }
        }

        foreach ($classes as $bootstrapper) {
            if (method_exists($bootstrapper, "router")) {
                $container->call([$bootstrapper, "router"], [$app]);
            }
        }

        foreach ($classes as $bootstrapper) {
            if (method_exists($bootstrapper, "booted")) {
                $container->call([$bootstrapper, "booted"]);
            }
        }
    }

    /**
     * @return class-string<Bootstrapper>[]
     * @throws \Throwable
     */
    protected final function getSorted(): array
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
            /** @var class-string<Bootstrapper> $class */
            $dg->requires($class, call_user_func([$class, "requires"]));
        }

        return $dg;
    }

    private static function validateBootstrapperClasses(array $classes): void
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
