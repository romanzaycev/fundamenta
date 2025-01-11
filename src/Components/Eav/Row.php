<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav;

interface Row
{
    public function getId(): int;
    public function getType(): string;
    public function getCreatedAt(): \DateTimeInterface;
    public function getUpdatedAt(): \DateTimeInterface;
    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array;
}
