<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav;

readonly class Value
{
    public function __construct(
        public int $id,
        public int $entityId,
        public int $attributeId,
        public ?string $valueVarchar,
        public ?string $valueText,
        public ?int $valueInteger,
        public ?float $valueNumeric,
        public ?bool $valueBool,
        public ?\DateTimeInterface $valueDate,
        public \DateTimeInterface $createdAt,
        public \DateTimeInterface $updatedAt,
        public ?string $description,
    ) {}
}
