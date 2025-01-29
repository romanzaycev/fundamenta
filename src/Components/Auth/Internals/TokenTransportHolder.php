<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth\Internals;

use Romanzaycev\Fundamenta\Components\Auth\Transport\HttpTransport;
use Romanzaycev\Fundamenta\Components\Auth\TransportSource;
use Romanzaycev\Fundamenta\Exceptions\Domain\EntityNotFoundException;

class TokenTransportHolder implements TransportSource
{
    /**
     * @var HttpTransport[]
     */
    private array $transports = [];

    public function getTransports(): array
    {
        return $this->transports;
    }

    public function register(HttpTransport $transport): void
    {
        $this->transports[$transport::class] = $transport;
    }

    public function getTransport(string $class): HttpTransport
    {
        return $this->transports[$class] ?? throw new EntityNotFoundException();
    }
}
