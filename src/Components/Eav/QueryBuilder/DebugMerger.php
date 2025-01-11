<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\QueryBuilder;

use Romanzaycev\Fundamenta\Components\Eav\Operator;

class DebugMerger implements LogicMerger
{
    use GenericMerger;

    protected function visit(array &$parts, string $field, Operator $op, mixed $value): void
    {
        $strOp = $op->value;

        if ($op === Operator::IN && is_array($value)) {
            $inValues = array_map(function($val) {
                return is_numeric($val) ? $val : "'" . $val . "'";
            }, $value);
            $valueStr = '(' . implode(', ', $inValues) . ')';
            $parts[] = "($field $strOp $valueStr)";
        } else {
            $formattedValue = is_numeric($value) ? $value : "'" . $value . "'";
            $parts[] = "($field $strOp $formattedValue)";
        }
    }
}
