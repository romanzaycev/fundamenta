<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Infrastructure\Pgsql\Traits;

use Cycle\Database\DatabaseInterface;
use Romanzaycev\Fundamenta\Infrastructure\Pgsql\Helper;

trait IsTableExistsTrait
{
    protected function isTableExists(DatabaseInterface $database, string $pgSchema, string $table): bool
    {
        $result = $database
            ->query(
            /** @lang PostgreSQL */"SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = :ts AND table_name = :tn)",
                [
                    "ts" => $pgSchema,
                    "tn" => $table,
                ]
            )->fetchColumn(0)
        ;

        return Helper::convertBool($result);
    }
}
