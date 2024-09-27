<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Server\OpenSwoole;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\UploadedFile;
use OpenSwoole\Core\Psr\Stream;
use OpenSwoole\Http\Server;
use Psr\Http\Message\ServerRequestInterface;
use Romanzaycev\Fundamenta\Configuration;

final class OpenSwooleHelper
{
    public static function handle(Server $server, callable $callback, Configuration $configuration): void
    {
        $ignoreFavicon = $configuration->get("openswoole.misc.ignore_favicon", true);
        $server->on("request", function (\OpenSwoole\HTTP\Request $request, \OpenSwoole\HTTP\Response $response) use (&$callback, $ignoreFavicon) {
            $emptyNotFoundResponse = new Response(404);

            if ($ignoreFavicon && $request->getMethod() === "GET" && $request->server["request_uri"] === "/favicon.ico") {
                SwoolePsrResponseHelper::emit($response, $emptyNotFoundResponse);
                return;
            }

            $serverRequest = self::from($request);
            $serverResponse = $callback($serverRequest);
            SwoolePsrResponseHelper::emit($response, $serverResponse);
        });
    }

    private static function from(\OpenSwoole\HTTP\Request $request): ServerRequestInterface
    {
        /** @var UploadedFile[] $files */
        $files = [];

        if (isset($request->files)) {
            foreach ($request->files as $name => $fileData) {
                $files[$name] = new UploadedFile(
                    Stream::createStreamFromFile($fileData["tmp_name"]),
                    $fileData["size"],
                    $fileData["error"],
                    $fileData["name"],
                    $fileData["type"]
                );
            }
        }

        return (new ServerRequest(
            $request->server["request_method"],
            $request->server["request_uri"],
            $request->header,
            $request->rawContent() ? $request->rawContent() : "php://memory",
        ))
            ->withUploadedFiles($files)
            ->withCookieParams($request->cookie ?? []);
    }
}
