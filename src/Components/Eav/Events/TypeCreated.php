<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Events;

use Romanzaycev\Fundamenta\Components\Eav\Type;

use Symfony\Contracts\EventDispatcher\Event;

class TypeCreated extends Event
{
    public const EVENT = "fnda.eav.type.created";

    public function __construct(
        private readonly Type $type,
    ) {}

    public function getEntity(): Type
    {
        return $this->type;
    }
}
