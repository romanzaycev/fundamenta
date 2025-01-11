<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav;

/**
 * @phpstan-type FilterValue string|float|bool|null|int|\DateTimeInterface|array
 */
class Query
{
    /**
     * @var string[]
     */
    protected array $selected = [];

    protected ?int $limit = null;
    protected ?int $offset = null;
    protected ?Logic $where = null;

    /** @var array<string, Order> */
    protected array $order = [];

    public function __construct(
        protected readonly string $entityType,
    ) {}

    public function select(string ...$attribute): self
    {
        $this->selected = $attribute;

        return $this;
    }

    /**
     * @param array<string, mixed|array{0: mixed, 1: Operator}> | Logic[] $where
     * @phpstan-param array<string, FilterValue|array{0: FilterValue, 1: Operator}> | Logic[] $where
     * @return $this
     */
    public function where(array $where): self
    {
        if (!empty($where)) {
            $this->where = Logic::and($where);
        }

        return $this;
    }

    /**
     * @param array<string, Order|string> $order
     * @return $this
     */
    public function order(array $order): self
    {
        if (!empty($order)) {
            foreach ($order as $attr => $d) {
                if (!is_string($attr)) {
                    throw new \InvalidArgumentException();
                }

                if (is_string($d)) {
                    $de = Order::tryFrom($d);

                    if (!$de) {
                        throw new \InvalidArgumentException("Invalid order direction string for field \"$attr\"");
                    }

                    $order[$attr] = $de;
                } else if (!$d instanceof Order) {
                    throw new \InvalidArgumentException("Invalid order direction for field \"$attr\"");
                }
            }

            $this->order = $order;
        }

        return $this;
    }

    public function limit(?int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function offset(?int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    public function perPage(?int $limit): self
    {
        return $this->limit($limit);
    }

    public function page(int $page): self
    {
        if ($page > 0 && $this->limit) {
            $this->offset = $this->limit * ($page - 1);
        }

        return $this;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * @return string[]
     */
    public function getSelected(): array
    {
        return $this->selected;
    }

    public function getWhere(): ?Logic
    {
        return $this->where;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }
}
