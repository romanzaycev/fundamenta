<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Admin\Internals\Providers;

use Cycle\Database\DatabaseInterface;
use Romanzaycev\Fundamenta\Components\Admin\AdminUser;
use Romanzaycev\Fundamenta\Components\Admin\AdminUserProvider;
use Romanzaycev\Fundamenta\Components\Auth\Token;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\Infrastructure\Pgsql\Helper;
use Romanzaycev\Fundamenta\Infrastructure\Pgsql\Traits\IsTableExistsTrait;

class PgsqlUserProvider implements AdminUserProvider
{
    use IsTableExistsTrait;

    protected bool $isTableInitialized = false;
    private string $schema;
    private string $table;

    public function __construct(
        private readonly DatabaseInterface $database,
        private readonly Configuration     $configuration,
    )
    {
        $this->schema = $this->configuration->get("admin.providers.user.pgsql.schema", "public");
        $this->table = $this->configuration->get("admin.providers.user.pgsql.table", "admin_users");
    }

    public function getUser(Token $token): ?AdminUser
    {
        $payload = $token->getPayload();

        if (isset($payload["is_adm"], $payload["adm_uuid"])) {
            if ($payload["is_adm"] === true && !empty($payload["adm_uuid"])) {
                $this->ensureSchema();
                $res = $this
                    ->database
                    ->query(
                        "SELECT * FROM $this->table WHERE id = :id AND is_active = true LIMIT 1",
                        [
                            "id" => $payload["adm_uuid"],
                        ]
                    );

                if ($row = $res->fetch()) {
                    return $this->map($row);
                }
            }
        }

        return null;
    }

    public function getByLogin(string $login): ?AdminUser
    {
        if (empty($login)) {
            return null;
        }

        $this->ensureSchema();
        $res = $this
            ->database
            ->query(
                "SELECT * FROM $this->table WHERE login = :login",
                [
                    "login" => $login,
                ],
            );

        if ($row = $res->fetch()) {
            return $this->map($row);
        }

        return null;
    }

    /**
     * @return AdminUser[]
     */
    public function getList(): array
    {
        $this->ensureSchema();
        $result = [];
        $res = $this
            ->database
            ->query(
                "SELECT * FROM $this->table",
            );

        while ($row = $res->fetch()) {
            $result[] = $this->map($row);
        }

        return $result;
    }

    public function update(AdminUser $user): void
    {
        $this->ensureSchema();
        $this
            ->database
            ->update(
                $this->table,
                [
                    "name" => $user->getName(),
                    "hash" => $user->getPasswordHash(),
                    "is_active" => $user->isActive(),
                    "totp_secret" => $user->getTotpSecret(),
                    "last_ua" => $user->getLastUa(),
                    "last_ip" => $user->getLastIp(),
                    "last_login" => $user->getLastLogin()?->format(DATE_ATOM),
                ],
                [
                    "id" => $user->getId(),
                ]
            )
            ->run();
    }

    protected function map(array $row): AdminUser
    {
        return new AdminUser(
            id: $row["id"],
            login: $row["login"],
            name: $row["name"],
            passwordHash: $row["hash"],
            isActive: Helper::convertBool($row["is_active"]),
            lastLogin: $row["last_login"]
                ? Helper::convertDbDateToNative($row["last_login"])
                : null,
            lastUa: !empty($row["last_ua"]) ? $row["last_ua"] : null,
            lastIp: !empty($row["last_ip"]) ? $row["last_ip"] : null,
            totpSecret: !empty($row["totp_secret"]) ? $row["totp_secret"] : null,
        );
    }

    protected function ensureSchema(): void
    {
        if ($this->isTableInitialized) {
            return;
        }

        if ($this->isTableExists($this->database, $this->schema, $this->table)) {
            $this->isTableInitialized = true;
            return;
        }

        $this->database->query(sprintf("
            CREATE TABLE %s (
               id UUID PRIMARY KEY,
               login VARCHAR(255) NOT NULL,
               name VARCHAR(255) NOT NULL,
               hash VARCHAR(255) NOT NULL,
               is_active BOOLEAN NOT NULL DEFAULT TRUE,
               last_login DATE NULL DEFAULT NULL,
               last_ua TEXT NULL DEFAULT NULL,
               last_ip INET NULL DEFAULT NULL,
               totp_secret VARCHAR(1024) NULL DEFAULT NULL
            );
        ", $this->table));
        $this->isTableInitialized = true;
    }
}
