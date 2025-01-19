<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Infrastructure\Pgsql\Traits;

use Cycle\Database\DatabaseInterface;

trait IsTableExistsTrait
{
    protected function isTableExists(DatabaseInterface $database, string $table): bool
    {
        $result = $database
            ->query(
            /** @lang PostgreSQL */"SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = :ts AND table_name = :tn)",
                [
                    "ts" => $this->schema,
                    "tn" => $table,
                ]
            )->fetchColumn(0)
        ;

        return $result === "true" || $result === true;
    }
}
