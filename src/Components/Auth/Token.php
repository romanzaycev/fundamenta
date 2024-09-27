<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth;

interface Token
{
    public function getId(): string;
    public function expiresAt(): ?\DateTimeInterface;
    public function getPayload(): array;
}
