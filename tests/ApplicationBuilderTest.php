<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Tests;

use DI\ContainerBuilder;
use Mockery;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Romanzaycev\Fundamenta\Application;
use Romanzaycev\Fundamenta\ApplicationBuilder;
use Romanzaycev\Fundamenta\Components\Configuration\ConfigurationLoader;
use Romanzaycev\Fundamenta\Components\Server\OpenSwoole\ServerFactory;
use Romanzaycev\Fundamenta\Components\Server\OpenSwoole\SwooleStaticHandler;
use Romanzaycev\Fundamenta\Components\Startup\Bootstrapper;
use Romanzaycev\Fundamenta\Tests\Stubs\StubErrorHandler;
use Romanzaycev\Fundamenta\Tests\Stubs\TestBootstrapper;
use PHPUnit\Framework\TestCase;
use Swoole\Http\Server;

class ApplicationBuilderTest extends TestCase
{
    private string $appPath = '/path/to/app';
    private string $dotenvPath = '/path/to/.env';

    private ConfigurationLoader|Mockery\MockInterface $configurationLoader;
    private ServerFactory|Mockery\MockInterface $swooleServerFactory;
    private Server|Mockery\MockInterface $server;

    protected function setUp(): void
    {
        $this->configurationLoader = Mockery::mock(ConfigurationLoader::class);
        $this->swooleServerFactory = Mockery::mock(ServerFactory::class);
        $this->server = Mockery::mock(Server::class);

        $this
            ->swooleServerFactory
            ->shouldReceive("createServer")
            ->andReturn($this->server);

        $this
            ->server
            ->shouldReceive("on");
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @param class-string<Bootstrapper>[] $bootstrappers
     */
    private function getInstance(
        array $bootstrappers,
        ContainerBuilder $containerBuilderStub,
    ): ApplicationBuilder
    {
        return new class (
            $this->appPath,
            $this->dotenvPath,
            $this->configurationLoader,
            $bootstrappers,
            $containerBuilderStub,
        ) extends ApplicationBuilder {
            public function __construct(
                string $appPath,
                string $dotenvPath,
                ConfigurationLoader $configurationLoader,
                array $bootstrappers,
                private readonly ContainerBuilder $containerBuilder,
            )
            {
                parent::__construct(
                    $appPath,
                    $dotenvPath,
                    $configurationLoader,
                    $bootstrappers,
                );
            }

            protected function createContainerBuilder(): ContainerBuilder
            {
                return $this->containerBuilder;
            }
        };
    }

    /**
     * @throws \Throwable
     */
    public function testBuildSuccessfullyCreatesApplication(): void
    {
        // @phpstan-ignore-next-line
        $this
            ->configurationLoader
            ->shouldReceive("load")
            ->once()
            ->andReturn([
                "slim" => [
                    "error_handler" => StubErrorHandler::class,
                    "error_middleware" => [
                        "display_error_details" => false,
                        "log_errors" => false,
                        "log_error_details" => false,
                    ],
                    "middlewares" => [],
                ],
                "openswoole" => [],
            ]);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(false);
        $containerBuilder->useAttributes(false);

        $containerBuilder->addDefinitions([
            LoggerInterface::class => \DI\create(NullLogger::class),
            ServerFactory::class => function () {
                return $this->swooleServerFactory;
            },
            SwooleStaticHandler::class => function () {
                return \Mockery::mock(SwooleStaticHandler::class);
            },
        ]);

        $appBuilder = $this->getInstance(
            [
                TestBootstrapper::class,
            ],
            $containerBuilder,
        );

        $result = $appBuilder->build();
        $this->assertInstanceOf(Application::class, $result);
    }
}
