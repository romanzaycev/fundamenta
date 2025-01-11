<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav;

readonly class Entity
{
    public function __construct(
        public int $id,
        public string $type,
        public \DateTimeInterface $createdAt,
        public \DateTimeInterface $updatedAt,
    ) {}
}

