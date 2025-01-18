<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql\Repositories;

use Cycle\Database\DatabaseInterface;
use Romanzaycev\Fundamenta\Components\Eav\Attribute;
use Romanzaycev\Fundamenta\Components\Eav\AttributeHelper;
use Romanzaycev\Fundamenta\Components\Eav\AttributeType;
use Romanzaycev\Fundamenta\Components\Eav\Events\AttributeCreated;
use Romanzaycev\Fundamenta\Components\Eav\Events\AttributeDeleted;
use Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql\Repositories\Helpers\PgsqlDateHelper;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\AttributeRepositoryInterface;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\SchemaInitializerInterface;
use Romanzaycev\Fundamenta\Configuration;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

readonly class PgsqlAttributeRepository implements AttributeRepositoryInterface
{
    private string $table;
    private string $typeTable;

    public function __construct(
        private DatabaseInterface $database,
        private Configuration $configuration,
        private SchemaInitializerInterface $schemaInitializer,
        private EventDispatcherInterface $eventDispatcher,
    )
    {
        $this->table = $this->configuration->get("eav.schema.tables.attribute");
        $this->typeTable = $this->configuration->get("eav.schema.tables.type");
    }

    public function create(
        int           $typeId,
        string        $code,
        AttributeType $type,
    ): Attribute
    {
        $code = AttributeHelper::normalizeAttributeCode($code);

        if (mb_strlen($code) > 100) {
            throw new \InvalidArgumentException();
        }

        if (AttributeHelper::isEntityOwned($code)) {
            throw new \InvalidArgumentException("Invalid attribute code \"$code\"");
        }

        $this->schemaInitializer->initialize();
        $stmt = $this->database->query(/** @lang PostgreSQL */"
            INSERT INTO $this->table (type_id, code, data_type)
            VALUES (:type_id, :code, :data_type)
            ON CONFLICT (type_id, code) DO UPDATE SET data_type = EXCLUDED.data_type
            RETURNING *
        ", [
            "type_id" => $typeId,
            "code" => $code,
            "data_type" => $type->value,
        ]);
        $attribute = $this->map($stmt->fetch());
        $this
            ->eventDispatcher
            ->dispatch(
                new AttributeCreated($attribute),
                AttributeCreated::EVENT,
            );

        return $attribute;
    }

    public function findByCode(int $typeId, string $code): ?Attribute
    {
        $this->schemaInitializer->initialize();
        $stmt = $this->database->query(
            /** @lang PostgreSQL */"SELECT * FROM $this->table WHERE type_id = :type_id AND code = :code LIMIT 1",
            [
                "type_id" => $typeId,
                "code" => $code,
            ]
        );

        if ($data = $stmt->fetch()) {
            return $this->map($data);
        }

        return null;
    }

    public function findById(int $id): ?Attribute
    {
        $this->schemaInitializer->initialize();
        $stmt = $this->database->query(
            /** @lang PostgreSQL */"SELECT * FROM $this->table WHERE id = :id LIMIT 1",
            ["id" => $id]
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
                [
                    "id" => $id,
                ]
            );
        $this
            ->eventDispatcher
            ->dispatch(
                new AttributeDeleted(
                    $id,
                ),
                AttributeDeleted::EVENT,
            );
    }

    public function getList(int $typeId): array
    {
        $this->schemaInitializer->initialize();
        $result = [];
        $stmt = $this->database->query(
            /** @lang PostgreSQL */"SELECT * FROM $this->table WHERE type_id = :type_id",
            [
                "type_id" => $typeId,
            ]
        );

        while ($row = $stmt->fetch()) {
            $result[] = $this->map($row);
        }

        return $result;
    }

    public function getListByEntityTypeCode(string $entityTypeCode): array
    {
        $this->schemaInitializer->initialize();
        $t = $this->table;
        $tt = $this->typeTable;
        $result = [];
        $stmt = $this->database->query(
            /** @lang PostgreSQL */"
                SELECT
                    $t.*
                FROM
                    $t
                JOIN $tt ON $t.type_id = $tt.id
                WHERE $tt.code = :entity_type_code
            ",
            [
                "entity_type_code" => $entityTypeCode,
            ]
        );

        while ($row = $stmt->fetch()) {
            $result[] = $this->map($row);
        }

        return $result;
    }

    protected function map(array $row): Attribute
    {
        return new Attribute(
            (int)$row["id"],
            (int)$row["type_id"],
            $row["code"],
            AttributeType::from($row["data_type"]),
            PgsqlDateHelper::toNative($row["created_at"]),
            PgsqlDateHelper::toNative($row['updated_at']),
        );
    }
}
