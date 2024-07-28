<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta;

use OpenSwoole\Server;
use Slim\App;

class Application
{
    public function __construct(
        private readonly Server $server,
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
