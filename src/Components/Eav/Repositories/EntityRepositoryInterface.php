<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Repositories;

use Romanzaycev\Fundamenta\Components\Eav\Entity;

interface EntityRepositoryInterface
{
    public function create(int $typeId): Entity;

    public function findById(int $id): ?Entity;

    public function delete(int $id): void;
}
