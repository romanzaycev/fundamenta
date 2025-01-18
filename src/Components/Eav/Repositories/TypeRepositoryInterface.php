<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Repositories;

use Romanzaycev\Fundamenta\Components\Eav\Type;

interface TypeRepositoryInterface
{
    public function create(string $code): Type;

    public function findById(int $id): ?Type;

    public function findByCode(string $code): ?Type;

    public function delete(int $id): void;
}
