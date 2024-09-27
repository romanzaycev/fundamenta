<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth\Session;

use Romanzaycev\Fundamenta\Components\Auth\Token;

readonly class SessionToken implements Token
{
    public function __construct(
        private string $id,
        private array $payload,
        private ?\DateTimeInterface $expiresAt,
    ) {}

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

    public static function unpack(array $data): SessionToken
    {
        $expiresAt = null;

        if ($data["e"] !== null) {
            $expiresAt = (new \DateTimeImmutable())->setTimestamp($data["e"]);
        }

        return new self(
            $data["i"],
            $data["p"],
            $expiresAt,
        );
    }

    public function pack(): array
    {
        return [
            'i' => $this->id,
            'e' => $this->expiresAt?->getTimestamp(),
            'p' => $this->payload,
        ];
    }
}
