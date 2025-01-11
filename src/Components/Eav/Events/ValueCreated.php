<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Events;

use Romanzaycev\Fundamenta\Components\Eav\Value;
use Symfony\Contracts\EventDispatcher\Event;

class ValueCreated extends Event
{
    public const EVENT = "fnda.eav.value.created";

    public function __construct(
        private readonly Value $value,
    ) {}

    public function getValue(): Value
    {
        return $this->value;
    }
}
