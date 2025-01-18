<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql;

use Romanzaycev\Fundamenta\Components\Eav\AttributeHelper;
use Romanzaycev\Fundamenta\Components\Eav\Operator;

class QueryContext
{
    /** @var array<string, mixed> */
    private array $bindings = [];

    /** @var string[] */
    private array $attributesCodes = [];

    /**
     * @return string[]|null
     */
    public function getSelectedAttributesCodes(): ?array
    {
        return !empty($this->attributesCodes)
            ? array_values(array_unique($this->attributesCodes))
            : null;
    }

    public function addSelectedAttributesCode(string $code): void
    {
        if (AttributeHelper::isEntityOwned($code)) {
            return;
        }

        if (!in_array($code, $this->attributesCodes)) {
            $this->attributesCodes[] = $code;
        }
    }

    public function bindReturnPlaceholder(
        string $mappedValCode,
        Operator $operator,
        mixed $value,
    ): string
    {
        if (in_array($operator, [Operator::IS, Operator::IS_NOT]) && $value === null) {
            return "";
        }

        $isMultiple = is_array($value);

        if ($isMultiple && $operator !== Operator::IN) {
            throw new \InvalidArgumentException();
        }

        $mappedValCode = str_replace(".", "_", $mappedValCode);
        $placeholders = [];

        if ($isMultiple) {
            foreach (array_values($value) as $i => $v) {
                $placeholders[] = ":" . $mappedValCode . "_" . $i;
            }
        } else {
            $placeholders[] = ":" . $mappedValCode;
        }

        if ($isMultiple) {
            foreach (array_values($value) as $i => $v) {
                $this->bindings[$mappedValCode . "_" . $i] = $v;
            }
        } else {
            $this->bindings[$mappedValCode] = $value;
        }

        return implode(", ", $placeholders);
    }

    /**
     * @return array<string, mixed>
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function bind(string $placeholder, mixed $value): void
    {
        $this->bindings[$placeholder] = $value;
    }
}
