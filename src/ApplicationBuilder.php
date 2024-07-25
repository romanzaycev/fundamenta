<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta;

use DI\ContainerBuilder;
use OpenSwoole\Server;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Romanzaycev\Fundamenta\Configuration\ConfigurationLoader;
use Romanzaycev\Fundamenta\Http\Server\OpenSwoole\OpenSwooleHelper;
use Romanzaycev\Fundamenta\Http\Server\OpenSwoole\ServerFactory;
use Romanzaycev\Fundamenta\Modules\ModulesConfigurator;
use Slim\App;
use Slim\Factory\AppFactory;

class ApplicationBuilder
{
    public function __construct(
        private readonly string $appPath,
        private readonly string $appNamespace,
        private readonly string $dotenvPath,
        private readonly ConfigurationLoader $configurationLoader,
    ) {}

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Throwable
     */
    public function build(): Application
    {
        $containerBuilder = $this->createContainerBuilder();
        $configuration = $this->createContainerConfiguration();

        $modulesConfigurator = $this->createModulesConfigurator($containerBuilder, $configuration);
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

        OpenSwooleHelper::handle($server, function (ServerRequestInterface $request) use ($slimApp) {
            return $slimApp->handle($request);
        }, $configuration);

        $app = $this->createApplication($slimApp, $server);
        $container->set(Application::class, $app);

        $modulesConfigurator->configureRouters($container);

        return $app;
    }

    protected function createContainerBuilder(): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);
        $containerBuilder->useAutowiring(false);

        return $containerBuilder;
    }

    protected function createContainerConfiguration(): Configuration
    {
        return new Configuration(
            $this->configurationLoader,
            [
                "app" => [
                    "path" => $this->appPath,
                    "namespace" => $this->appNamespace,
                ],

                "dotenv" => [
                    "path" => $this->dotenvPath,
                ],
            ],
        );
    }

    protected function createModulesConfigurator(ContainerBuilder $containerBuilder, Configuration $configuration): ModulesConfigurator
    {
        return new ModulesConfigurator($configuration, $containerBuilder);
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
        );
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        foreach ($slimConfig["middlewares"] as $middleware) {
            $slimApp->add($middleware);
        }
    }

    protected function createApplication(App $slimApp, Server $server): Application
    {
        return new Application($slimApp, $server);
    }
}
