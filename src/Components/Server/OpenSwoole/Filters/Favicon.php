<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Server\OpenSwoole\Filters;

use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;
use Romanzaycev\Fundamenta\Components\Server\OpenSwoole\FilterInterface;
use Romanzaycev\Fundamenta\Components\Server\OpenSwoole\RequestHandlerInterface;
use Romanzaycev\Fundamenta\Components\Server\OpenSwoole\SwoolePsrResponseHelper;

class Favicon implements FilterInterface
{
    public function handle(Request $request, Response $response, RequestHandlerInterface $handler): void
    {
        $isGetMethod = $request->getMethod() === "GET";
        $requestPath = $request->server["request_uri"];

        if ($isGetMethod && $requestPath === "/favicon.ico") {
            SwoolePsrResponseHelper::emit($response, new \Nyholm\Psr7\Response(404));
            return;
        }

        $handler->handle($request, $response);
    }

    public function getSorting(): int
    {
        return -1;
    }
}
