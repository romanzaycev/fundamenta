<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Events;

use Romanzaycev\Fundamenta\Components\Eav\Attribute;
use Symfony\Contracts\EventDispatcher\Event;

class AttributeCreated extends Event
{
    public const EVENT = "fnda.eav.attribute.created";

    public function __construct(
        private readonly Attribute $attribute,
    ) {}

    public function getAttribute(): Attribute
    {
        return $this->attribute;
    }
}
