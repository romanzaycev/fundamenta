<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Repositories;

use Romanzaycev\Fundamenta\Components\Eav\Attribute;
use Romanzaycev\Fundamenta\Components\Eav\AttributeType;

interface AttributeRepositoryInterface
{
    public function create(
        int $entityId,
        string $code,
        AttributeType $type,
    ): Attribute;

    public function findByName(
        int $entityId,
        string $code,
    ): ?Attribute;

    public function findById(int $id): ?Attribute;

    public function delete(int $id): void;

    /**
     * @return Attribute[]
     */
    public function getList(int $entityId): array;

    /**
     * @return Attribute[]
     */
    public function getListByEntityType(string $entityType): array;
}
