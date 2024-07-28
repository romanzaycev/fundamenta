<?php declare(strict_types=1);

use Romanzaycev\Devsite\Site;
use Romanzaycev\Fundamenta\ApplicationBuilder;
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
        ]),
        [
            Site::class
        ]
    ));
    $builder->build()->start();
})();
