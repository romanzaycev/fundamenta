<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql;

use Romanzaycev\Fundamenta\Components\Eav\Attribute;

readonly class EntityMetadata
{
    /**
     * @param array<string, Attribute> $attributes
     */
    public function __construct(
        public array $attributes,
    ) {}
}
