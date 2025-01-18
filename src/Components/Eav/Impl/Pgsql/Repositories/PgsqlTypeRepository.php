<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql\Repositories;

use Cycle\Database\DatabaseInterface;
use Romanzaycev\Fundamenta\Components\Eav\Events\TypeCreated;
use Romanzaycev\Fundamenta\Components\Eav\Events\TypeDeleted;
use Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql\Repositories\Helpers\PgsqlDateHelper;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\SchemaInitializerInterface;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\TypeRepositoryInterface;
use Romanzaycev\Fundamenta\Components\Eav\Type;
use Romanzaycev\Fundamenta\Configuration;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class PgsqlTypeRepository implements TypeRepositoryInterface
{
    private string $table;

    public function __construct(
        private DatabaseInterface $database,
        private Configuration $configuration,
        private SchemaInitializerInterface $schemaInitializer,
        private EventDispatcherInterface $eventDispatcher,
    )
    {
        $this->table = $this->configuration->get("eav.schema.tables.type");
    }

    public function create(string $code): Type
    {
        if (mb_strlen($code) > 100) {
            throw new \InvalidArgumentException();
        }

        $this->schemaInitializer->initialize();
        $stmt = $this
            ->database
            ->query(
                /** @lang PostgreSQL */"
                    INSERT INTO $this->table (code) VALUES (:code)
                    ON CONFLICT (code) DO UPDATE SET code = EXCLUDED.code
                    RETURNING *
                ",
                ["code" => $code],
            );
        $type = $this->map($stmt->fetch());
        $this
            ->eventDispatcher
            ->dispatch(
                new TypeCreated($type),
                TypeCreated::EVENT,
            );

        return $type;
    }

    public function findById(int $id): ?Type
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

    public function findByCode(string $code): ?Type
    {
        $this->schemaInitializer->initialize();
        $stmt = $this
            ->database
            ->query(
            /** @lang PostgreSQL */"SELECT * FROM $this->table WHERE code = :code LIMIT 1",
                ["code" => $code],
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
                new TypeDeleted($id),
                TypeDeleted::EVENT,
            );
    }

    protected function map(array $row): Type
    {
        return new Type(
            (int)$row["id"],
            $row["code"],
            PgsqlDateHelper::toNative($row["created_at"]),
            PgsqlDateHelper::toNative($row["updated_at"]),
        );
    }
}
