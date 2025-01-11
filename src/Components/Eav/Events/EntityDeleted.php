<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Events;

use Symfony\Contracts\EventDispatcher\Event;

class EntityDeleted extends Event
{
    public const EVENT = "fnda.eav.entity.deleted";

    public function __construct(
        private readonly int $entityId,
    ) {}

    public function getEntityId(): int
    {
        return $this->entityId;
    }
}
