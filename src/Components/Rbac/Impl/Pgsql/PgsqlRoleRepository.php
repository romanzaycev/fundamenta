<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Rbac\Impl\Pgsql;

use Cycle\Database\DatabaseInterface;
use Romanzaycev\Fundamenta\Components\Rbac\Role;
use Romanzaycev\Fundamenta\Components\Rbac\RoleHolder;
use Romanzaycev\Fundamenta\Components\Rbac\RoleRepository;
use Romanzaycev\Fundamenta\Components\Rbac\Subject;
use Romanzaycev\Fundamenta\Configuration;

class PgsqlRoleRepository implements RoleRepository
{
    private string $table;

    public function __construct(
        private readonly DatabaseInterface $database,
        private readonly SchemaInitializer $schemaInitializer,
        private readonly RoleHolder $roleHolder,
        private readonly Configuration $configuration,
    )
    {
        $this->table = $this->configuration->get("rbac.schema.tables.role", "rbac_roles");
    }

    public function getBySubject(Subject|string $subjectOrId): array
    {
        $this->schemaInitializer->initialize();
        $result = [];
        $subjectId = $subjectOrId instanceof Subject ? $subjectOrId->getSubjectId() : $subjectOrId;
        $res = $this
            ->database
            ->query(
                sprintf("SELECT role FROM %s WHERE subject_id = :subject_id", $this->table),
                [
                    "subject_id" => $subjectId,
                ],
            );

        while ($row = $res->fetch()) {
            if ($role = $this->roleHolder->get($row["role"])) {
                $result[] = $role;
            }
        }

        return $result;
    }

    public function add(Subject|string $subjectOrId, Role|string $roleOrCode): void
    {
        $this
            ->database
            ->query(
            /** @lang PostgreSQL */"
                    INSERT INTO $this->table (subject_id, role) VALUES (:subject_id, :role)
                    ON CONFLICT (subject_id, role) DO NOTHING
                ",
                [
                    "subject_id" => $subjectOrId instanceof Subject ? $subjectOrId->getSubjectId() : $subjectOrId,
                    "role" => $roleOrCode instanceof Role ? $roleOrCode->getCode() : $roleOrCode,
                ],
            );
    }

    public function remove(Subject|string $subjectOrId, Role|string $roleOrCode): void
    {
        $this
            ->database
            ->query(
                /** @lang PostgreSQL */"
                    DELETE FROM $this->table WHERE subject_id = :subject_id AND role = :role
                ",
                [
                    "subject_id" => $subjectOrId instanceof Subject ? $subjectOrId->getSubjectId() : $subjectOrId,
                    "role" => $roleOrCode instanceof Role ? $roleOrCode->getCode() : $roleOrCode,
                ],
            );
    }
}
