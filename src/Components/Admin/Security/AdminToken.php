<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Admin\Security;

use Romanzaycev\Fundamenta\Components\Auth\Token;

class AdminToken implements Token
{
    private string $id;

    public function __construct(
        private readonly \DateTimeInterface $expiresAt,
        private readonly array $payload,
    )
    {
        $this->id = base64_encode(random_bytes(64));
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function expiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
