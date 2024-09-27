<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth\Transport;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HttpTransport
{
    public function get(ServerRequestInterface $request): ?string;

    public function commit(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $id,
        \DateTimeInterface $expiresAt = null,
    ): ResponseInterface;

    public function remove(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $id,
    ): ResponseInterface;
}
