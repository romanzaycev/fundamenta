<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Configuration;

class ArrayLoader implements ConfigurationLoader
{
    public function __construct(
        private readonly array $sections,
    ) {}

    public function load(): array
    {
        return $this->sections;
    }
}
