<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Server\OpenSwoole;

interface FilterCollection
{
    /**
     * @param FilterInterface|class-string<FilterInterface> $filter
     */
    public function add(FilterInterface|string $filter): void;
}
