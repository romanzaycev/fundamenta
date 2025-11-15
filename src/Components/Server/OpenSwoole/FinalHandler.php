<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Server\OpenSwoole;

use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\UploadedFile;
use OpenSwoole\Core\Psr\Stream;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;
use Psr\Http\Message\ServerRequestInterface;

class FinalHandler implements RequestHandlerInterface
{
    /** @var callable|null */
    private $callback = null;

    public function setCallback(?callable $callback): void
    {
        $this->callback = $callback;
    }

    public function handle(Request $request, Response $response): void
    {
        if (!$this->callback) {
            throw new \RuntimeException("Empty callback");
        }

        $serverRequest = self::from($request);
        $serverResponse = call_user_func($this->callback, $serverRequest);
        SwoolePsrResponseHelper::emit($response, $serverResponse);
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

        $psrRequest = (new ServerRequest(
            $request->server["request_method"],
            $request->server["request_uri"],
            $request->header,
            $request->rawContent() ? $request->rawContent() : "php://memory",
            serverParams: array_change_key_case($request->server, CASE_UPPER),
        ))
            ->withUploadedFiles($files)
            ->withCookieParams($request->cookie ?? [])
        ;

        if ($request->getMethod() === "GET") {
            $psrRequest = $psrRequest->withQueryParams($request->get ?? []);
        }

        return $psrRequest;
    }
}
