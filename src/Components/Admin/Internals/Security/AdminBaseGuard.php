<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Admin\Internals\Security;

use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Romanzaycev\Fundamenta\Components\Server\OpenSwoole\FilterInterface;
use Romanzaycev\Fundamenta\Components\Server\OpenSwoole\RequestHandlerInterface as OpenSwooleRequestHandler;
use Romanzaycev\Fundamenta\Configuration;
use Slim\Exception\HttpNotFoundException;

class AdminBaseGuard implements MiddlewareInterface, FilterInterface
{
    /** @var string[] */
    private array $allowedHosts;

    /** @var string[] */
    private array $basePaths = [];

    private bool $isEnabled = false;

    public function __construct(
        private readonly Configuration $configuration,
    )
    {
        $this->allowedHosts = $this->configuration->get("admin.security.allowed_hosts", []);
        $pathsCfg = $this->configuration->get("admin.paths", []);

        if (!empty($pathsCfg)) {
            if (isset($pathsCfg["ui_base_path"])) {
                $this->basePaths[] = $pathsCfg["ui_base_path"];
            }

            if (isset($pathsCfg["ui_api_base_path"])) {
                $this->basePaths[] = $pathsCfg["ui_api_base_path"];
            }
        }

        $this->isEnabled = !empty($this->allowedHosts);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isEnabled) {
            if ($this->isPathMatched($request->getRequestTarget())) {
                $host = $this->normalizeHost($request->getHeaderLine("Host"));

                if (!in_array($host, $this->allowedHosts, true)) {
                    throw new HttpNotFoundException($request);
                }
            }
        }

        return $handler->handle($request);
    }

    public function handle(Request $request, Response $response, OpenSwooleRequestHandler $handler): void
    {
        if ($this->isEnabled) {
            if ($this->isPathMatched($request->server["request_uri"])) {
                $host = $this->normalizeHost($request->header["host"] ?? '');

                if (!in_array($host, $this->allowedHosts, true)) {
                    $response->status(404);
                    $response->end();
                    return;
                }
            }
        }

        $handler->handle($request, $response);
    }

    public function getSorting(): int
    {
        return -1;
    }

    private function normalizeHost(string $host): string
    {
        $host = \mb_strtolower($host);

        if (\str_contains($host, ":")) {
            $tmp = explode(":", $host);
            $host = $tmp[0];
        }

        return $host;
    }

    private function isPathMatched(string $requestTarget): bool
    {
        foreach ($this->basePaths as $basePath) {
            if (\str_starts_with($requestTarget, $basePath)) {
                return true;
            }
        }

        return false;
    }
}
