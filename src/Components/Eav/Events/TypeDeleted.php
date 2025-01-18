<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Events;

use Symfony\Contracts\EventDispatcher\Event;

class TypeDeleted extends Event
{
    public const EVENT = "fnda.eav.type.deleted";

    public function __construct(
        private readonly int $typeId,
    ) {}

    public function getTypeId(): int
    {
        return $this->typeId;
    }
}
