<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql\Repositories;

use Cycle\Database\DatabaseInterface;
use Romanzaycev\Fundamenta\Components\Eav\Entity;
use Romanzaycev\Fundamenta\Components\Eav\Events\EntityCreated;
use Romanzaycev\Fundamenta\Components\Eav\Events\EntityDeleted;
use Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql\Repositories\Helpers\PgsqlDateHelper;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\EntityRepositoryInterface;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\SchemaInitializerInterface;
use Romanzaycev\Fundamenta\Configuration;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class PgsqlEntityRepository implements EntityRepositoryInterface
{
    private string $table;

    public function __construct(
        private DatabaseInterface $database,
        private Configuration $configuration,
        private SchemaInitializerInterface $schemaInitializer,
        private EventDispatcherInterface $eventDispatcher,
    )
    {
        $this->table = $this->configuration->get("eav.schema.tables.entity");
    }

    public function create(string $type): Entity
    {
        $this->schemaInitializer->initialize();
        $stmt = $this
            ->database
            ->query(
                /** @lang PostgreSQL */"INSERT INTO $this->table (type) VALUES (:type) RETURNING *",
                ["type" => $type],
            );
        $entity = $this->map($stmt->fetch());
        $this
            ->eventDispatcher
            ->dispatch(
                new EntityCreated($entity),
                EntityCreated::EVENT,
            );

        return $entity;
    }

    public function findById(int $id): ?Entity
    {
        $this->schemaInitializer->initialize();
        $stmt = $this
            ->database
            ->query(
                /** @lang PostgreSQL */"SELECT * FROM $this->table WHERE id = :id LIMIT 1",
                ["id" => $id],
            );

        if ($data = $stmt->fetch()) {
            return $this->map($data);
        }

        return null;
    }

    public function delete(int $id): void
    {
        $this->schemaInitializer->initialize();
        $this
            ->database
            ->query(
                /** @lang PostgreSQL */"DELETE FROM $this->table WHERE id = :id",
                ["id" => $id],
            );
        $this
            ->eventDispatcher
            ->dispatch(
                new EntityDeleted($id),
                EntityDeleted::EVENT,
            );
    }

    protected function map(array $row): Entity
    {
        return new Entity(
            (int)$row["id"],
            $row["type"],
            PgsqlDateHelper::toNative($row["created_at"]),
            PgsqlDateHelper::toNative($row["updated_at"]),
        );
    }
}
