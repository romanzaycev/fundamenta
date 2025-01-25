<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Server\OpenSwoole;

use OpenSwoole\Http\Server;

final class OpenSwooleHelper
{
    public static function handle(
        Server $server,
        callable $callback,
        FilterPipeline $pipeline,
    ): void
    {
        $server->on(
            "request",
            function (
                \OpenSwoole\HTTP\Request $request,
                \OpenSwoole\HTTP\Response $response,
            ) use (
                &$callback,
                $pipeline,
            ) {
                $pipeline->handle($request, $response, $callback);
            }
        );
    }
}
