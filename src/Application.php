<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta;

use DI\Container;
use OpenSwoole\Http\Server;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Romanzaycev\Fundamenta\Components\Http\HttpHelper;
use Romanzaycev\Fundamenta\Components\Server\OpenSwoole\FilterPipeline;
use Romanzaycev\Fundamenta\Components\Server\OpenSwoole\OpenSwooleHelper;
use Romanzaycev\Fundamenta\Components\Startup\HookManager;
use Slim\App;

class Application
{
    private bool $isStarted = false;

    public function __construct(
        protected readonly Server $server,
        protected readonly App $slim,
        protected readonly Container $container,
        protected readonly HookManager $hookManager,
        protected readonly LoggerInterface $logger,
    ) {}

    /**
     * @throws \Throwable
     */
    public function start(): void
    {
        if ($this->isStarted) {
            return;
        }

        $serverErrorResponse = HttpHelper::html("Server error", 500);
        OpenSwooleHelper::handle(
            $this->server,
            function (ServerRequestInterface $request) use (
                $serverErrorResponse,
            ) {
                try {
                    $this
                        ->hookManager
                        ->call(
                            $this->container,
                            HookManager::ON_REQUEST,
                            $request,
                        );
                    $result = $this->slim->handle($request);
                    $this
                        ->hookManager
                        ->call(
                            $this->container,
                            HookManager::ON_REQUEST_TERMINATED,
                        );

                    return $result;
                } catch (\Throwable $e) {
                    $this
                        ->logger
                        ->error(
                            "[OpenSwooleHelper::handle] Unhandled exception: " . $e->getMessage(),
                            [
                                "exception" => $e,
                            ],
                        );
                }

                return $serverErrorResponse;
            },
            $this->container->get(FilterPipeline::class),
        );
        $this->server->on("start", function () {
            $this->hookManager->call($this->container, HookManager::ON_SERVER_STARTED);
        });
        $this->server->start();
        $this->isStarted = true;
    }

    public function stop(): void
    {
        $this->server->shutdown();
    }
}
