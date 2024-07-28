<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Configuration;

interface ConfigurationLoader
{
    public function load(): array;
}
