<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth\Transport;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class HeaderTransport implements HttpTransport
{
    public function __construct(
        private string $header = "X-Auth-Token",
        private string $format = "%s",
    ) {}

    public function get(ServerRequestInterface $request): ?string
    {
        if ($request->hasHeader($this->header)) {
            return $this->extractToken($request);
        }

        return null;
    }

    public function commit(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $id,
        \DateTimeInterface $expiresAt = null,
    ): ResponseInterface
    {
        return $response;
    }

    public function remove(ServerRequestInterface $request, ResponseInterface $response, string $id): ResponseInterface
    {
        return $response;
    }

    private function extractToken(ServerRequestInterface $request): ?string
    {
        $headerLine = $request->getHeaderLine($this->header);

        if ($this->format !== '%s') {
            [$token] = sscanf($headerLine, $this->format);

            return $token !== null ? (string)$token : null;
        }

        return $headerLine;
    }
}
