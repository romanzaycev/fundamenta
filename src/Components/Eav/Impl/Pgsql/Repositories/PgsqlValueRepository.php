<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql\Repositories;

use Cycle\Database\DatabaseInterface;
use Romanzaycev\Fundamenta\Components\Eav\Attribute;
use Romanzaycev\Fundamenta\Components\Eav\AttributeType;
use Romanzaycev\Fundamenta\Components\Eav\Events\ValueCreated;
use Romanzaycev\Fundamenta\Components\Eav\Events\ValueDeleted;
use Romanzaycev\Fundamenta\Components\Eav\Events\ValueUpdated;
use Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql\Repositories\Helpers\PgsqlDateHelper;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\AttributeRepositoryInterface;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\SchemaInitializerInterface;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\ValueRepositoryInterface;
use Romanzaycev\Fundamenta\Components\Eav\Value;
use Romanzaycev\Fundamenta\Configuration;
use Symfony\Component\EventDispatcher\EventDispatcher;

readonly class PgsqlValueRepository implements ValueRepositoryInterface
{
    private string $table;

    public function __construct(
        private DatabaseInterface $database,
        private Configuration $configuration,
        private AttributeRepositoryInterface $attributeRepository,
        private SchemaInitializerInterface $schemaInitializer,
        private EventDispatcher $eventDispatcher,
    )
    {
        $this->table = $this->configuration->get("eav.schema.tables.value");
    }

    public function create(
        int $entityId,
        int $attributeId,
        int|float|bool|string|\DateTimeInterface $value,
        ?string $description = null,
    ): Value
    {
        $this->schemaInitializer->initialize();
        $attribute = $this
            ->attributeRepository
            ->findById($attributeId);

        if (!$attribute) {
            throw new \InvalidArgumentException('Attribute not found.');
        }

        $stmt = $this->database->query(/** @lang PostgreSQL */"
            INSERT INTO $this->table (
                entity_id,
                attribute_id,
                value_varchar,
                value_text,
                value_integer,
                value_numeric,
                value_bool,
                value_date,
                description
            ) VALUES (
                :entity_id,
                :attribute_id,
                :value_varchar,
                :value_text,
                :value_integer,
                :value_numeric,
                :value_boole,
                :value_date,
                :description
            )
            ON CONFLICT (entity_id, attribute_id) 
            DO UPDATE SET 
                value_varchar = EXCLUDED.value_varchar,
                value_text = EXCLUDED.value_text,
                value_integer = EXCLUDED.value_integer,
                value_numeric = EXCLUDED.value_numeric,
                value_bool = EXCLUDED.value_bool,
                value_date = EXCLUDED.value_date,
                description = EXCLUDED.description,
                updated_at = NOW()
            RETURNING *
        ", $this->createFields($entityId, $attribute, $value, $description));
        $vInstance = $this->map($stmt->fetch());
        $this
            ->eventDispatcher
            ->dispatch(
                new ValueCreated($vInstance),
                ValueCreated::EVENT,
            );

        return $vInstance;
    }

    public function find(int $entityId, int $attributeId): ?Value
    {
        $this->schemaInitializer->initialize();
        $stmt = $this
            ->database
            ->query(
                'SELECT * FROM $this->table WHERE entity_id = :entity_id AND attribute_id = :attribute_id',
                [
                    'entity_id' => $entityId,
                    'attribute_id' => $attributeId,
                ],
            );

        if ($data = $stmt->fetch()) {
            return $this->map($data);
        }

        return null;
    }

    public function update(
        int $entityId,
        int $attributeId,
        int|float|bool|string|\DateTimeInterface $value,
        ?string $description = null,
    ): bool
    {
        $this->schemaInitializer->initialize();
        $attribute = $this->attributeRepository->findById($attributeId);

        if (!$attribute) {
            throw new \InvalidArgumentException('Attribute not found.');
        }

        $stmt = $this->database->query("
            UPDATE $this->table 
            SET 
                value_varchar = :value_varchar,
                value_text = :value_text,
                value_integer = :value_integer,
                value_numeric = :value_numeric,
                value_bool = :value_bool,
                value_date = :value_date,
                description = :description,
                updated_at = NOW()
            WHERE entity_id = :entity_id AND attribute_id = :attribute_id
        ", $this->createFields($entityId, $attribute, $value, $description));
        $isUpdated = $stmt->rowCount() > 0;

        if (!$isUpdated) {
            $this
                ->eventDispatcher
                ->dispatch(
                    new ValueUpdated(
                        $entityId,
                        $attributeId,
                        $value,
                        $description,
                    ),
                    ValueUpdated::EVENT,
                );
        }

        return $isUpdated;
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
                new ValueDeleted($id),
                ValueDeleted::EVENT,
            );
    }

    protected function createFields(
        int $entityId,
        Attribute $attribute,
        int|float|bool|string|\DateTimeInterface $value,
        ?string $description,
    ): array
    {
        $fields = [
            'entity_id' => $entityId,
            'attribute_id' => $attribute->id,
            'value_varchar' => null,
            'value_text' => null,
            'value_integer' => null,
            'value_numeric' => null,
            'value_bool' => null,
            'value_date' => null,
            'description' => null,
        ];

        switch ($attribute->type) {
            case AttributeType::VARCHAR:
                if (mb_strlen((string)$value) > 512) {
                    throw new \RuntimeException();
                }

                $fields['value_varchar'] = (string)$value;
                break;

            case AttributeType::TEXT:
                $fields['value_text'] = (string)$value;
                break;

            case AttributeType::INTEGER:
                $fields['value_integer'] = (int)$value;
                break;

            case AttributeType::NUMERIC:
                $fields['value_numeric'] = (float)$value;
                break;

            case AttributeType::BOOL:
                $fields['value_bool'] = (bool)$value;
                break;

            case AttributeType::DATE_TIME:
                if (!$value instanceof \DateTimeInterface) {
                    throw new \RuntimeException();
                }

                $fields['value_date'] = PgsqlDateHelper::fromNativeAttribute($value);
                break;

            default:
                throw new \InvalidArgumentException("Unsupported data type: " . $attribute->type->name);
        }

        if (!is_null($description)) {
            $fields["description"] = mb_substr($description, 0, 512);
        }

        return $fields;
    }

    protected function map(array $row): Value
    {
        return new Value(
            (int)$row["id"],
            (int)$row["entity_id"],
            (int)$row["attribute_id"],
            $row["value_varchar"],
            $row["value_text"],
            isset($row["value_integer"])
                ? (int)$row["value_integer"]
                : null,
            isset($row["value_numeric"])
                ? (float)$row["value_numeric"]
                : null,
            isset($row["value_bool"])
                ? (bool)$row["value_bool"]
                : null,
            isset($row["value_date"])
                ? PgsqlDateHelper::toNative($row["value_date"])
                : null,
            PgsqlDateHelper::toNative($row["created_at"]),
            PgsqlDateHelper::toNative($row["updated_at"]),
            $row["description"],
        );
    }
}
