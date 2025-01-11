<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Events;

use Symfony\Contracts\EventDispatcher\Event;

class AttributeDeleted extends Event
{
    public const EVENT = "fnda.eav.attribute.deleted";

    public function __construct(
        private readonly int $attributeId,
    ) {}

    public function getAttributeId(): int
    {
        return $this->attributeId;
    }
}
