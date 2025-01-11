<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Events;

use Symfony\Contracts\EventDispatcher\Event;

class ValueUpdated extends Event
{
    public const EVENT = "fnda.eav.value.updated";

    public function __construct(
        private int $entityId,
        private int $attributeId,
        private int|float|bool|string|\DateTimeInterface $value,
        private ?string $description = null,
    ) {}

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function getAttributeId(): int
    {
        return $this->attributeId;
    }

    public function getValue(): float|\DateTimeInterface|bool|int|string
    {
        return $this->value;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
