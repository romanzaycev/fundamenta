<?php declare(strict_types=1);

use Romanzaycev\Devsite\Site;
use Romanzaycev\Fundamenta\ApplicationBuilder;
use Romanzaycev\Fundamenta\Components\Auth\Session\SessionTokenStorage;
use Romanzaycev\Fundamenta\Components\Configuration\ArrayLoader;

require dirname(__DIR__) . "/vendor/autoload.php";

(function () {
    OpenSwoole\Runtime::enableCoroutine(true, OpenSwoole\Runtime::HOOK_ALL);
    $builder = (new ApplicationBuilder(
        dirname(__DIR__),
        __DIR__,
        new ArrayLoader([
            "openswoole" => [
                "settings" => [
                    "document_root" => __DIR__ . "/public",
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
        ]),
        [
            Site::class,
        ]
    ));
    $builder->build()->start();
})();
