<?php declare(strict_types=1);

use Romanzaycev\Devsite\Site;
use Romanzaycev\Fundamenta\ApplicationBuilder;
use Romanzaycev\Fundamenta\Components\Auth\Session\SessionTokenStorage;
use Romanzaycev\Fundamenta\Components\Configuration\ArrayLoader;
use Cycle\Database\Config as DbConfig;
use Romanzaycev\Fundamenta\Components\Configuration\Env;
use Romanzaycev\Fundamenta\Components\Configuration\LazyIntEnv;
use Romanzaycev\Fundamenta\Components\Configuration\LazyValue;

require dirname(__DIR__) . "/vendor/autoload.php";

(function () {
    OpenSwoole\Runtime::enableCoroutine(true, OpenSwoole\Runtime::HOOK_ALL);
    $builder = (new ApplicationBuilder(
        dirname(__DIR__),
        __DIR__,
        new ArrayLoader([
            "admin" => [
                "security" => [
                    "allowed_hosts" => [
                        "localhost",
                    ],
                    "auth" => [
                        "totp_required" => true,
                    ],
                ],
            ],
            "slim" => [
                "error_middleware" => [
                    "display_error_details" => true,
                ],
            ],
            "openswoole" => [
                "settings" => [
                    "document_root" => __DIR__ . "/public",
                    "max_request" => new LazyIntEnv("SWOOLE_MAX_REQUEST", 1),
                ],
            ],
            "tooolooop" => [
                "directory" => __DIR__ . "/views",
            ],
            "session" => [
                "secret_key" => '8le7hlrpprNiQY663gXqPHw6dyhUt9kjUDuwBva1UfpL52ILXYeEI4i3QzSDnjQZOYckx3urSM/gj8a36DCoOw==', //base64_encode(random_bytes(64)),
            ],
            "auth" => [
                "enabled" => true,
                "storage" => SessionTokenStorage::class,
            ],
            "dbal" => [
                "databases" => [
                    "default" => [
                        "connection" => "pgsql",
                    ],
                ],
                "connections" => new LazyValue(static fn () => [
                    "pgsql" => new DbConfig\PostgresDriverConfig(
                        connection: new DbConfig\Postgres\DsnConnectionConfig(
                            sprintf(
                                "pgsql:host=localhost;port=%d;dbname=%s",
                                Env::getInt("PG_PORT", 0),
                                Env::getString("PG_DB", ""),
                            ),
                            Env::getString("PG_USER", ""),
                            Env::getString("PG_PASSWORD", ""),
                        ),
                        queryCache: true,
                    ),
                ]),
            ],
        ]),
        [
            Site::class,
        ]
    ));
    $builder->build()->start();
})();
