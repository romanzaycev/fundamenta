<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth;

use Romanzaycev\Fundamenta\Components\Auth\Transport\HttpTransport;

class Context
{
    private bool $isClosed = false;

    private ?Token $token = null;

    /** @var class-string<HttpTransport>|null */
    private ?string $transport = null;

    /**
     * @param class-string<HttpTransport> $defaultTransport
     */
    public function __construct(
        private readonly TokenStorage $storage,
        private readonly string $defaultTransport,
    ) {}

    /**
     * @param class-string<HttpTransport> $transport
     */
    public function start(
        Token $token,
        string $transport,
    ): void
    {
        $this->token = $token;
        $this->transport = $transport;
        $this->isClosed = false;
    }

    public function isClosed(): bool
    {
        return $this->isClosed;
    }

    public function getToken(): ?Token
    {
        return $this->token;
    }

    /**
     * @return class-string<HttpTransport>|null
     */
    public function getTransport(): ?string
    {
        return $this->transport ?? $this->defaultTransport;
    }

    public function close(): void
    {
        $this->isClosed = true;
    }

    public function getStorage(): TokenStorage
    {
        return $this->storage;
    }
}
