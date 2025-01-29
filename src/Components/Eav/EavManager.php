<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav;

use Romanzaycev\Fundamenta\Components\Eav\Internals\Executor;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\AttributeRepositoryInterface;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\EntityRepositoryInterface;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\TypeRepositoryInterface;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\ValueRepositoryInterface;

readonly class EavManager
{
    public function __construct(
        private TypeRepositoryInterface $typeRepository,
        private EntityRepositoryInterface $entityRepository,
        private AttributeRepositoryInterface $attributeRepository,
        private ValueRepositoryInterface $valueRepository,
        private Executor $executor,
    ) {}

    public function getTypeRepository(): TypeRepositoryInterface
    {
        return $this->typeRepository;
    }

    public function getEntityRepository(): EntityRepositoryInterface
    {
        return $this->entityRepository;
    }

    public function getAttributeRepository(): AttributeRepositoryInterface
    {
        return $this->attributeRepository;
    }

    public function getValueRepository(): ValueRepositoryInterface
    {
        return $this->valueRepository;
    }

    /**
     * @param string[] $select
     * @param array<string, mixed|array{0: mixed, 1: Operator}> | Logic[] $where
     * @param array<string, Order|string> $order
     */
    public function query(
        string $entityTypeCode,
        array $select = [],
        array $where = [],
        array $order = [],
        ?int $limit = null,
        ?int $offset = null,
    ): RowSet
    {
        return $this->execute((new Query($entityTypeCode))
            ->select(...$select)
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->offset($offset)
        );
    }

    /**
     * @param array<string, mixed|array{0: mixed, 1: Operator}> | Logic[] $where
     */
    public function count(string $entityTypeCode, array $where = []): int
    {
        return $this->executor->count((new Query($entityTypeCode))->where($where));
    }

    public function execute(Query $query): RowSet
    {
        return $this->executor->execute($query);
    }
}
