<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac\Impl\Pgsql;

use Cycle\Database\DatabaseInterface;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\Infrastructure\Pgsql\Traits\IsTableExistsTrait;

class SchemaInitializer
{
    use IsTableExistsTrait;

    private bool $initialized = false;
    private readonly string $schema;
    private readonly string $rolesTable;

    public function __construct(
        private readonly Configuration $configuration,
        private readonly DatabaseInterface $database,
    )
    {
        $config = $this->configuration->get("rbac.schema");
        $this->schema = $config["pg_schema"] ?? "public";
        $this->rolesTable = $config["tables"]["role"] ?? "rbac_roles";
    }

    public function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        if (!$this->isTableExists($this->database, $this->rolesTable)) {
            $this->creatRolesTable();
        }

        $this->initialized = true;
    }

    private function creatRolesTable(): void
    {
        $t = $this->rolesTable;
        $this->execute([
            /** @lang PostgreSQL */"
            CREATE TABLE $t (
                subject_id VARCHAR NOT NULL,
                role VARCHAR NOT NULL
            );",
            "CREATE UNIQUE INDEX uq_rbac_roles_subj_id_role ON $t(subject_id, role);",
            "CREATE INDEX idx_rbac_roles_subj_id ON $t(subject_id);",
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
