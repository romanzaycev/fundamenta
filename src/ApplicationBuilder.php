<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta;

use DI\ContainerBuilder;
use OpenSwoole\Server;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Romanzaycev\Fundamenta\Components\Configuration\ConfigurationLoader;
use Romanzaycev\Fundamenta\Components\Server\OpenSwoole\OpenSwooleHelper;
use Romanzaycev\Fundamenta\Components\Server\OpenSwoole\ServerFactory;
use Romanzaycev\Fundamenta\Components\Server\OpenSwoole\SwooleStaticHandler;
use Romanzaycev\Fundamenta\Components\Startup\Bootstrapper;
use Romanzaycev\Fundamenta\Components\Startup\HookManager;
use Romanzaycev\Fundamenta\Components\Startup\ModulesConfigurator;
use Romanzaycev\Fundamenta\Components\Startup\ApplicationHookManager;
use Slim\App;
use Slim\Factory\AppFactory;

class ApplicationBuilder
{
    public function __construct(
        private readonly string $appPath,
        private readonly string $dotenvPath,
        private readonly ConfigurationLoader $configurationLoader,
        private array $bootstrappers,
    ) {}

    /**
     * @param class-string<Bootstrapper> $bootstrapper
     * @return $this
     */
    public function add(string $bootstrapper): self
    {
        $this->bootstrappers[] = $bootstrapper;

        return $this;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Throwable
     */
    public function build(): Application
    {
        $containerBuilder = $this->createContainerBuilder();
        $configuration = $this->createConfiguration();

        $modulesConfigurator = $this->createModulesConfigurator(
            $containerBuilder,
            $configuration,
        );
        $modulesConfigurator->preconfigure();
        $configurationErrors = $configuration->validate();

        if (!empty($configurationErrors)) {
            throw new \RuntimeException("Configuration errors: " . implode("; ", $configurationErrors));
        }

        $containerBuilder->addDefinitions([
            Configuration::class => static fn () => $configuration,
        ]);
        $modulesConfigurator->boot();
        $container = $containerBuilder->build();

        AppFactory::setContainer($container);
        $slimApp = AppFactory::create();
        $container->set(App::class, $slimApp);
        $this->configureSlim($slimApp, $configuration);

        $serverFactory = $container->get(ServerFactory::class);
        $server = $serverFactory->createServer();
        $hookManager = $this->createHookManager($container->get(LoggerInterface::class));
        $container->set(HookManager::class, $hookManager);

        OpenSwooleHelper::handle(
            $server,
            function (ServerRequestInterface $request) use ($slimApp, $container, $hookManager) {
                $hookManager->call($container, HookManager::ON_REQUEST, $request);
                $result = $slimApp->handle($request);
                $hookManager->call($container, HookManager::ON_REQUEST_TERMINATED);

                return $result;
            },
            $configuration,
            $container->get(SwooleStaticHandler::class),
        );

        $app = $this->createApplication($server);
        $container->set(Application::class, $app);

        $modulesConfigurator->afterContainerBuilt($container);

        return $app;
    }

    protected function createContainerBuilder(): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);
        $containerBuilder->useAttributes(true);

        return $containerBuilder;
    }

    protected function createConfiguration(): Configuration
    {
        return new Configuration(
            $this->configurationLoader,
            [
                "app" => [
                    "path" => $this->appPath,
                ],

                "dotenv" => [
                    "path" => $this->dotenvPath,
                ],
            ],
        );
    }

    protected function createModulesConfigurator(
        ContainerBuilder $containerBuilder,
        Configuration $configuration,
    ): ModulesConfigurator
    {
        return new ModulesConfigurator(
            $configuration,
            $containerBuilder,
            $this->bootstrappers,
        );
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Throwable
     */
    protected function configureSlim(App $slimApp, Configuration $configuration): void
    {
        $slimApp->addBodyParsingMiddleware();
        $slimApp->addRoutingMiddleware();
        $slimConfig = $configuration->get("slim");
        $errorHandlerClass = $slimConfig["error_handler"];
        $errorHandler = new $errorHandlerClass(
            $slimApp->getCallableResolver(),
            $slimApp->getResponseFactory(),
            $slimApp->getContainer()->get(LoggerInterface::class),
        );
        $errorMiddleware = $slimApp->addErrorMiddleware(
            $slimConfig["error_middleware"]["display_error_details"],
            $slimConfig["error_middleware"]["log_errors"],
            $slimConfig["error_middleware"]["log_error_details"],
            $slimApp->getContainer()->get(LoggerInterface::class),
        );
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        foreach ($slimConfig["middlewares"] as $middleware) {
            $slimApp->add($middleware);
        }
    }

    protected function createApplication(Server $server): Application
    {
        return new Application($server);
    }

    protected function createHookManager(LoggerInterface $logger): HookManager
    {
        return new ApplicationHookManager($logger);
    }
}
