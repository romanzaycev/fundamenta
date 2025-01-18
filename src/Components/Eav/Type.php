<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav;

readonly class Type
{
    public function __construct(
        public int $id,
        public string $code,
        public \DateTimeInterface $createdAt,
        public \DateTimeInterface $updatedAt,
    ) {}
}
