<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\QueryBuilder;

use Romanzaycev\Fundamenta\Components\Eav\Logic;
use Romanzaycev\Fundamenta\Components\Eav\Operator;

trait GenericMerger
{
    public function merge(array $compiledNodes): string
    {
        return $this->iterateCompiledNodes($compiledNodes);
    }

    protected function iterateCompiledNodes(array $conditions): string
    {
        if (empty($conditions)) {
            return "";
        }

        $operator = strtoupper(array_shift($conditions));

        if (!in_array($operator, [Logic::OP_AND, Logic::OP_OR])) {
            throw new \InvalidArgumentException("Invalid logical operator: $operator");
        }

        $parts = [];

        foreach ($conditions[0] as $condition) {
            if (!is_array($condition) || empty($condition)) {
                continue;
            }

            $firstElement = strtoupper($condition[0]);

            if (in_array($firstElement, [Logic::OP_AND, Logic::OP_OR])) {
                $nestedCondition = $this->iterateCompiledNodes($condition);
                $parts[] = "($nestedCondition)";
            } else {
                /** @var Operator $op */
                [$field, $op, $value] = $condition;
                $this->visit($parts, $field, $op, $value);
            }
        }

        return implode(" $operator ", $parts);
    }

    protected abstract function visit(array &$parts, string $field, Operator $op, mixed $value): void;
}
