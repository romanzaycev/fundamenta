<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Events;

use Symfony\Contracts\EventDispatcher\Event;

class ValueDeleted extends Event
{
    public const EVENT = "fnda.eav.value.deleted";

    public function __construct(
        private readonly int $valueId,
    ) {}

    public function getValueId(): int
    {
        return $this->valueId;
    }
}
