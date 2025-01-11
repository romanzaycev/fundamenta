<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\QueryBuilder;

interface LogicMerger
{
    public function merge(array $compiledNodes): string;
}
