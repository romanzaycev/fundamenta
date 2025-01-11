<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav;

use Cycle\Database\StatementInterface;

class RowSet implements \IteratorAggregate
{
    private bool $isCached = false;
    private bool $isClosed = false;
    private ?int $totalCount = null;

    /** @var \SplFixedArray<Row>|null */
    private ?\SplFixedArray $cache = null;

    public function __construct(
        private readonly StatementInterface $result,
        private readonly \Closure $totalCountFetcher,
        private readonly \Closure $rowDecorator,
    ) {}

    /**
     * @return \Traversable<Row>
     */
    public function getIterator(): \Traversable
    {
        if ($this->isClosed) {
            return;
        }

        if ($this->isCached) {
            foreach ($this->cache as $row) {
                yield $row;
            }

            return;
        }

        $this->cache = new \SplFixedArray($this->result->rowCount());
        $i = 0;

        foreach ($this->result as $item) {
            $row = call_user_func($this->rowDecorator, $item);
            $this->cache[$i++] = $row;

            yield $row;
        }

        $this->isCached = true;
    }

    public function getTotalCount(): int
    {
        if ($this->totalCount === null) {
            $this->totalCount = call_user_func($this->totalCountFetcher);
        }

        return $this->totalCount;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close(): void
    {
        if (!$this->isClosed) {
            $this->isClosed = true;
            $this->cache = null;

            try {
                $this->result->close();
            } catch (\Throwable $_) {}
        }
    }
}
