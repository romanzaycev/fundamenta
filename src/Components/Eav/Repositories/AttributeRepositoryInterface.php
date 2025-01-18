<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Repositories;

use Romanzaycev\Fundamenta\Components\Eav\Attribute;
use Romanzaycev\Fundamenta\Components\Eav\AttributeType;

interface AttributeRepositoryInterface
{
    public function create(
        int $typeId,
        string $code,
        AttributeType $type,
    ): Attribute;

    public function findByCode(
        int $typeId,
        string $code,
    ): ?Attribute;

    public function findById(int $id): ?Attribute;

    public function delete(int $id): void;

    /**
     * @return Attribute[]
     */
    public function getList(int $typeId): array;

    /**
     * @return Attribute[]
     */
    public function getListByEntityTypeCode(string $entityTypeCode): array;
}
