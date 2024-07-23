<?php declare(strict_types=1);

use Romanzaycev\Fundamenta\ApplicationBuilder;
use Romanzaycev\Fundamenta\Configuration\ArrayLoader;

require dirname(__DIR__) . "/vendor/autoload.php";

(function () {
    $app = (new ApplicationBuilder(
        dirname(__DIR__),
        "",
        __DIR__,
        new ArrayLoader([
            "openswoole" => [
                "settings" => [
                    "document_root" => __DIR__ . "/public",
                ],
            ],
        ]),
    ))->build();
    $app->start();
})();
