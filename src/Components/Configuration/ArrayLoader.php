<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Configuration;

readonly class ArrayLoader implements ConfigurationLoader
{
    /**
     * @param mixed[] $sections
     */
    public function __construct(
        private array $sections,
    ) {}

    public function load(): array
    {
        return $this->sections;
    }
}
