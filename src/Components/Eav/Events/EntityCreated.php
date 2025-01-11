<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Events;

use Romanzaycev\Fundamenta\Components\Eav\Entity;
use Symfony\Contracts\EventDispatcher\Event;

class EntityCreated extends Event
{
    public const EVENT = "fnda.eav.entity.created";

    public function __construct(
        private readonly Entity $entity,
    ) {}

    public function getEntity(): Entity
    {
        return $this->entity;
    }
}
