<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta;

use OpenSwoole\Server;

readonly class Application
{
    public function __construct(
        private Server $server,
    ) {}

    public function start(): void
    {
        $this->server->start();
    }

    public function stop(): void
    {
        $this->server->shutdown();
    }
}
