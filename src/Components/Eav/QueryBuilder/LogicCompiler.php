<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\QueryBuilder;

use Romanzaycev\Fundamenta\Components\Eav\InternalLogicOps;
use Romanzaycev\Fundamenta\Components\Eav\Logic;
use Romanzaycev\Fundamenta\Components\Eav\Operator;

readonly class LogicCompiler
{
    public function __construct(
        private Logic $logic,
    ) {}

    public function compile(): array
    {
        return $this->compileNode($this->logic);
    }

    protected function compileNode(Logic $node, ?string $field = null): array
    {
        switch ($node->getOp()) {
            case InternalLogicOps::OP_OR:
                $result = $this->createEmptySetNode($node->getOp());

                if (str_starts_with($field, InternalLogicOps::SINGLE_OR_PFX)) {
                    return $this->iterateWhere($node, $result);
                } else {
                    return $this->iterateWhere($node, $result, $field);
                }

            case InternalLogicOps::OP_AND:
                return $this->iterateWhere(
                    $node,
                    $this->createEmptySetNode($node->getOp()),
                );

            default:
                throw new \RuntimeException("Unknown logic operator: " . $node->getOp());
        }
    }

    protected function iterateWhere(Logic $node, array $result, ?string $field = null): array
    {
        foreach ($node->getWhere() as $k => $v) {
            $f = $field ?: $k;

            if ($v instanceof Logic) {
                $result[1][] = $this->compileNode($v, $f);
            } else {
                $result[1][] = $this->createResult($this->normalizeValue($v, $f), $f);
            }
        }

        return $result;
    }

    /**
     * @return array{0: string, 1: array[]}
     */
    protected function createEmptySetNode(string $op): array
    {
        return [
            $op,
            [],
        ];
    }

    /**
     * @param array{0: mixed, 1: Operator} $value
     * @param string $field
     * @return array
     */
    protected function createResult(array $value, string $field): array
    {
        return [
            $field,
            $value[1],
            $value[0],
        ];
    }

    protected function normalizeValue(mixed $value, string $field): array
    {
        if (is_array($value)) {
            if (!$value[1] instanceof Operator) {
                throw new \InvalidArgumentException(
                    "Missing operator for filter field \"$field\"",
                );
            }
        } else {
            $value = [$value, Operator::EQ];
        }

        return $value;
    }
}
