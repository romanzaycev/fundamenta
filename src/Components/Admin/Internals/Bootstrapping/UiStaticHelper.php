<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Admin\Internals\Bootstrapping;

use Psr\Log\LoggerInterface;
use Romanzaycev\Fundamenta\Components\Admin\Internals\Security\AdminBaseGuard;
use Romanzaycev\Fundamenta\Components\Configuration\Env;
use Romanzaycev\Fundamenta\Components\Http\Static\Directory;
use Romanzaycev\Fundamenta\Components\Http\Static\File;
use Romanzaycev\Fundamenta\Components\Http\Static\StaticHandler;
use Romanzaycev\Fundamenta\Components\Server\OpenSwoole\FilterCollection;
use Romanzaycev\Fundamenta\Configuration;

final readonly class UiStaticHelper
{
    public function __construct(
        private StaticHandler    $staticHandler,
        private Configuration    $configuration,
        private LoggerInterface  $logger,
        private FilterCollection $filterCollection,
        private string           $resourcesDir,
    ) {}

    public function configure(): void
    {
        if (!file_exists($this->resourcesDir) || !is_dir($this->resourcesDir)) {
            $this
                ->logger
                ->warning(
                    "[Admin] Bootstrapping error, ui directory not found",
                    [
                        "path" => $this->resourcesDir,
                    ],
                );
        } else {
            $basePath = $this->configuration->get("admin.paths.ui_base_path");
            $apiBasePath = $this->configuration->get("admin.paths.ui_api_base_path");
            $uiFiles = [
                new File(
                    $basePath . "/app.css",
                    $this->resourcesDir . "/app.css",
                ),
                new File(
                    $basePath . "/app.js",
                    $this->resourcesDir . "/app.js",
                ),
                new File(
                    $basePath . "/favicon.ico",
                    $this->resourcesDir . "/favicon.ico",
                ),
                new Directory(
                    $basePath . "/assets",
                    $this->resourcesDir . "/assets",
                ),
                (new File(
                    $basePath,
                    $this->resourcesDir . "/index.html",
                ))
                    ->setPreprocessor(
                        static fn (string $content): string => self::preprocessIndex(
                            $content,
                            $basePath,
                            $apiBasePath,
                        )
                    )
                    ->asVirtualRewrite(true),
            ];

            foreach ($uiFiles as $uiFile) {
                $this->staticHandler->add($uiFile);
            }

            $this
                ->filterCollection
                ->add(
                    AdminBaseGuard::class,
                );
        }
    }

    private static function preprocessIndex(string $content, string $basePath, string $apiBasePath): string
    {
        $__fndaapp = [
            "env" => [
                "APP_ENV" => Env::getString("APP_ENV", ""),
                "API_BASE_PATH" => $apiBasePath,
                "ROUTER_BASE_PATH" => $basePath,
            ],
        ];

        $content = str_replace("/app.", $basePath . "/app.", $content);
        $content = str_replace("</head>", "\n<script>window.__fndaapp=".json_encode($__fndaapp).";</script>\n" . "</head>", $content);

        return $content;
    }
}
