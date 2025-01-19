<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql\Repositories;

use Cycle\Database\DatabaseInterface;
use Romanzaycev\Fundamenta\Components\Eav\Repositories\SchemaInitializerInterface;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\Infrastructure\Pgsql\Traits\IsTableExistsTrait;

class PgsqlSchemaInitializer implements SchemaInitializerInterface
{
    use IsTableExistsTrait;

    private bool $initialized = false;
    private readonly string $schema;
    private readonly string $typesTable;
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
        $this->typesTable = $config["tables"]["type"];
        $this->entitiesTable = $config["tables"]["entity"];
        $this->attributesTable = $config["tables"]["attribute"];
        $this->valuesTable = $config["tables"]["value"];
    }

    public function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        if (!$this->isTableExists($this->database, $this->typesTable)) {
            $this->creatTypesTable();
        }

        if (!$this->isTableExists($this->database, $this->entitiesTable)) {
            $this->creatEntitiesTable();
        }

        if (!$this->isTableExists($this->database, $this->attributesTable)) {
            $this->creatAttributesTable();
        }

        if (!$this->isTableExists($this->database, $this->valuesTable)) {
            $this->createValuesTable();
        }

        $this->initialized = true;
    }

    private function creatTypesTable(): void
    {
        $t = $this->typesTable;
        $this->execute([
            /** @lang PostgreSQL */"
            CREATE TABLE $t (
                id SERIAL PRIMARY KEY,
                code VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW()
            );",
            "CREATE UNIQUE INDEX uq_eav_type_code ON $t(code);",
        ]);
    }

    private function creatEntitiesTable(): void
    {
        $t = $this->entitiesTable;
        $tt = $this->typesTable;
        $this->execute([
            /** @lang PostgreSQL */"
            CREATE TABLE $t (
                id SERIAL PRIMARY KEY,
                type_id INTEGER NOT NULL REFERENCES $tt(id) ON DELETE CASCADE,
                alias VARCHAR(512) NULL DEFAULT NULL,
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW()
            );",
            "CREATE INDEX idx_eav_entities_type ON $t(type_id);",
            "CREATE INDEX idx_eav_entities_alias ON $t(alias);",
        ]);
    }

    private function creatAttributesTable(): void
    {
        $t = $this->attributesTable;
        $tt = $this->typesTable;
        $this->execute([
            /** @lang PostgreSQL */"
            CREATE TABLE $t (
                id SERIAL PRIMARY KEY,
                type_id INTEGER NOT NULL REFERENCES $tt(id) ON DELETE CASCADE,
                code VARCHAR(100) NOT NULL,
                data_type VARCHAR(50) NOT NULL,
                created_at TIMESTAMP DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NOW()
            );",
            "CREATE UNIQUE INDEX uq_eav_attributes_type_id_code ON $t(type_id, code);",
            "CREATE INDEX idx_eav_attributes_data_type ON $t(data_type);",
            "CREATE INDEX idx_eav_attributes_type_id ON $t(type_id);",
            "CREATE INDEX idx_eav_attributes_code ON $t(code);",
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
            "CREATE INDEX idx_eav_values_entity_id ON $t(entity_id);",
            "CREATE INDEX idx_eav_values_attribute_id ON $t(attribute_id);",
            "CREATE INDEX idx_eav_values_entity_attr ON $t(entity_id, attribute_id);",
            "CREATE INDEX idx_eav_values_val_varchar ON $t(value_varchar);",
            "CREATE INDEX idx_eav_values_val_integer ON $t(value_integer);",
            "CREATE INDEX idx_eav_values_val_numeric ON $t(value_numeric);",
            "CREATE INDEX idx_eav_values_val_bool ON $t(value_bool);",
            "CREATE INDEX idx_eav_values_val_date ON $t(value_date);",
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
