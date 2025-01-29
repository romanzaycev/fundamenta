<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Internals;

use Romanzaycev\Fundamenta\Components\Eav\Query;
use Romanzaycev\Fundamenta\Components\Eav\RowSet;

interface Executor
{
    public function execute(Query $query): RowSet;

    public function count(Query $query): int;
}
