<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav;

interface Executor
{
    public function execute(Query $query): RowSet;

    public function count(Query $query): int;
}
