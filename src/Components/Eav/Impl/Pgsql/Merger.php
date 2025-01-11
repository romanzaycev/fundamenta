<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql;

use Romanzaycev\Fundamenta\Components\Eav\AttributeHelper;
use Romanzaycev\Fundamenta\Components\Eav\AttributeType;
use Romanzaycev\Fundamenta\Components\Eav\Exceptions\QueryException;
use Romanzaycev\Fundamenta\Components\Eav\Operator;
use Romanzaycev\Fundamenta\Components\Eav\Query;
use Romanzaycev\Fundamenta\Components\Eav\QueryBuilder\LogicMerger;
use Romanzaycev\Fundamenta\Components\Eav\QueryBuilder\GenericMerger;

class Merger implements LogicMerger
{
    use GenericMerger;

    private readonly bool $isSelected;

    public function __construct(
        private readonly Query $query,
        private readonly QueryContext $context,
        private readonly Materializer $materializer,
    )
    {
        $this->isSelected = $this->isSomeSelected();
    }

    /**
     * @throws QueryException
     */
    public function finalize(): void
    {
        $entityType = $this->query->getEntityType();
        $selected = $this->query->getSelected();

        if (!empty($selected)) {
            foreach ($selected as $code) {
                if (AttributeHelper::isDeniedCode($code)) {
                    continue;
                }

                if (!$this->materializer->isKnownAttribute($entityType, $code)) {
                    throw new QueryException("Unknown attribute \"$code\"");
                }

                $this->context->addAttributeCode($code);
            }
        }
    }

    /**
     * @throws QueryException
     */
    protected function visit(array &$parts, string $field, Operator $op, mixed $value): void
    {
        $field = AttributeHelper::normalizeAttributeCode($field);

        if (empty($field)) {
            throw new QueryException();
        }

        $strOp = null;

        if ($value === null && in_array($op, [Operator::IS, Operator::IS_NOT])) {
            $strOp = $op->value . " NULL";
        }

        if ($strOp === null) {
            $strOp = $op->value;
        }

        $isCodeCondNeeded = true;
        $valueColumn = "";

        if ($field === "id") {
            $field = "ee.id";
            $isCodeCondNeeded = false;
        } else {
            $attribute = $this
                ->materializer
                ->getAttribute(
                    $this->query->getEntityType(),
                    $field,
                );

            if (!$attribute) {
                $valueColumn = "notval";
            } else {
                if ($this->isSelected) {
                    $this->context->addAttributeCode($field);
                }

                $valueColumn = match ($attribute->type) {
                    AttributeType::DATE_TIME => "vd",
                    AttributeType::VARCHAR => "vv",
                    AttributeType::TEXT => "vt",
                    AttributeType::INTEGER => "vi",
                    AttributeType::NUMERIC => "vn",
                    AttributeType::BOOL => "vb",
                    // @phpstan-ignore-next-line
                    default => throw new QueryException("Unsupported attribute type " . $attribute->type->name),
                };
            }
        }

        $placeholder = $this->context->bindReturnPlaceholder($field, $op, $value);

        if ($op === Operator::IN && is_array($value)) {
            $placeholder = "(" . $placeholder . ")";
        }

        $parts[] = "(" . ($isCodeCondNeeded ? "mapped_val.code = '$field' AND mapped_val.$valueColumn" : "") . " $strOp $placeholder)";
    }

    private function isSomeSelected(): bool
    {
        $selected = $this->query->getSelected();

        if (!empty($selected)) {
            foreach ($selected as $code) {
                if (AttributeHelper::isDeniedCode($code)) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }
}
