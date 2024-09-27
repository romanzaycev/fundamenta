<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth;

use Romanzaycev\Fundamenta\Components\Auth\Transport\HttpTransport;
use Romanzaycev\Fundamenta\Exceptions\Domain\EntityNotFoundException;

interface TransportSource
{
    /**
     * @return HttpTransport[]
     */
    public function getTransports(): array;

    /**
     * @param class-string<HttpTransport> $class
     * @throws EntityNotFoundException
     */
    public function getTransport(string $class): HttpTransport;
}
