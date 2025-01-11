<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav;

readonly class Attribute
{
    public function __construct(
        public int $id,
        public int $entityId,
        public string $code,
        public AttributeType $type,
        public \DateTimeInterface $createdAt,
        public \DateTimeInterface $updatedAt,
    ) {}
}
