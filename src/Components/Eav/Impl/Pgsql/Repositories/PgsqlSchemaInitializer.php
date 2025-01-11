<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql\Repositories;

use Cycle\Database\DatabaseInterface;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\SchemaInitializerInterface;
use Romanzaycev\Fundamenta\Configuration;

class PgsqlSchemaInitializer implements SchemaInitializerInterface
{
    private bool $initialized = false;
    private readonly string $schema;
    private readonly string $entitiesTable;
    private readonly string $attributesTable;
    private readonly string $valuesTable;

    public function __construct(
        private readonly DatabaseInterface $database,
        private readonly Configuration $configuration,
    )
    {
        $config = $this->configuration->get("eav.schema");
        $this->schema = $config["pg_schema"];
        $this->entitiesTable = $config["tables"]["entity"];
        $this->attributesTable = $config["tables"]["attribute"];
        $this->valuesTable = $config["tables"]["value"];
    }

    public function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        if (!$this->isTableExists($this->entitiesTable)) {
            $this->creatEntitiesTable();
        }

        if (!$this->isTableExists($this->attributesTable)) {
            $this->creatAttributesTable();
        }

        if (!$this->isTableExists($this->valuesTable)) {
            $this->createValuesTable();
        }

        $this->initialized = true;
    }

    private function isTableExists(string $table): bool
    {
        $result = $this
            ->database
            ->query(
            /** @lang PostgreSQL */"SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = :ts AND table_name = :tn)",
                [
                    "ts" => $this->schema,
                    "tn" => $table,
                ]
            )->fetchColumn(0);

        return $result === "true" || $result === true;
    }

    private function creatEntitiesTable(): void
    {
        $t = $this->entitiesTable;
        $this->execute([
            /** @lang PostgreSQL */"
            CREATE TABLE $t (
                id SERIAL PRIMARY KEY,
                type VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW()
            );",
            "CREATE INDEX idx_entities_type ON $t(type);",
        ]);
    }

    private function creatAttributesTable(): void
    {
        $t = $this->attributesTable;
        $et = $this->entitiesTable;
        $this->execute([
            /** @lang PostgreSQL */"
            CREATE TABLE $t (
                id SERIAL PRIMARY KEY,
                entity_id INTEGER NOT NULL REFERENCES $et(id) ON DELETE CASCADE,
                code VARCHAR(100) NOT NULL,
                data_type VARCHAR(50) NOT NULL,
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW()
            );",
            "CREATE UNIQUE INDEX uq_attributes_code ON $t(entity_id, code);",
            "CREATE INDEX idx_attributes_data_type ON $t(data_type);",
            "CREATE INDEX idx_attributes_entity_id ON $t(entity_id);",
        ]);
    }

    private function createValuesTable(): void
    {
        $t = $this->valuesTable;
        $et = $this->entitiesTable;
        $at = $this->attributesTable;
        $this->execute([
            /** @lang PostgreSQL */"
            CREATE TABLE $t (
                id SERIAL PRIMARY KEY,
                entity_id INTEGER NOT NULL REFERENCES $et(id) ON DELETE CASCADE,
                attribute_id INTEGER NOT NULL REFERENCES $at(id) ON DELETE CASCADE,
                value_varchar VARCHAR(512),
                value_text TEXT,
                value_integer INTEGER,
                value_numeric NUMERIC,
                value_bool BOOL,
                value_date DATE,
                description VARCHAR(512),
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW(),
                UNIQUE (entity_id, attribute_id)
            );",
            "CREATE INDEX idx_values_entity_id ON $t(entity_id);",
            "CREATE INDEX idx_values_attribute_id ON $t(attribute_id);",
            "CREATE INDEX idx_values_entity_attr ON $t(entity_id, attribute_id);",
            "CREATE INDEX idx_values_val_varchar ON $t(value_varchar);",
            "CREATE INDEX idx_values_val_integer ON $t(value_integer);",
            "CREATE INDEX idx_values_val_numeric ON $t(value_numeric);",
            "CREATE INDEX idx_values_val_bool ON $t(value_bool);",
            "CREATE INDEX idx_values_val_date ON $t(value_date);",
        ]);
    }

    /**
     * @param string|string[] $sql
     */
    private function execute(string|array $sql): void
    {
        if (!is_array($sql)) {
            $sql = [$sql];
        }

        foreach ($sql as $s) {
            $this->database->query($s);
        }
    }
}
