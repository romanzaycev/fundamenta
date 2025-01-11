<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav;

use Romanzaycev\Fundamenta\Components\Eav\QueryBuilder\DebugMerger;
use Romanzaycev\Fundamenta\Components\Eav\QueryBuilder\LogicCompiler;

/**
 * @phpstan-type FilterValue string|float|bool|null|int|\DateTimeInterface|array
 */
readonly class Logic
{
    public const OP_AND = "AND";
    public const OP_OR = "OR";

    public function __construct(
        private string $op,
        private array $where,
    ) {}

    public function getOp(): string
    {
        return $this->op;
    }

    public function getWhere(): array
    {
        return $this->where;
    }

    /**
     * @param array<string|int, Logic|mixed> $where
     */
    public static function and(array $where): self
    {
        $additional = [];

        foreach ($where as $k => $v) {
            if ($v instanceof Logic) {
                if ($v->getOp() === self::OP_AND) {
                    foreach ($v->getWhere() as $kk => $vv) {
                        $additional[$kk] = $vv;
                    }
                    unset($where[$k]);
                    continue;
                }

                if ($v->getOp() === self::OP_OR && !is_string($k)) {
                    $additional["__single-or_" . mt_rand()] = $v;
                    unset($where[$k]);
                    continue;
                }
            }

            if (!is_string($k)) {
                throw new \InvalidArgumentException(
                    "Incorrect `where` for AND logic filter, lists are prohibited",
                );
            }
        }

        return new self(
            self::OP_AND,
            array_merge($where, $additional),
        );
    }

    /**
     * @param array<string|int, Logic|mixed> $where
     */
    public static function or(array $where): self
    {
        return new self(self::OP_OR, $where);
    }

    public function debug(): string
    {
        return (new DebugMerger())
            ->merge(
                (new LogicCompiler($this))->compile(),
            );
    }
}
