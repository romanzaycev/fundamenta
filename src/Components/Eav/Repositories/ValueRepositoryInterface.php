<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Repositories;

use Romanzaycev\Fundamenta\Components\Eav\Value;

interface ValueRepositoryInterface
{
    public function create(
        int $entityId,
        int $attributeId,
        int|float|bool|string|\DateTimeInterface $value,
        ?string $description = null,
    ): Value;

    public function find(int $entityId, int $attributeId): ?Value;

    public function update(
        int $entityId,
        int $attributeId,
        int|float|bool|string|\DateTimeInterface $value,
        ?string $description = null,
    ): bool;

    public function delete(int $id): void;
}
