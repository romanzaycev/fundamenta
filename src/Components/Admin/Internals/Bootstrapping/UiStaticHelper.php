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

class UiStaticHelper
{
    protected bool $isConfigured = false;

    public function __construct(
        private readonly StaticHandler    $staticHandler,
        private readonly Configuration    $configuration,
        private readonly LoggerInterface  $logger,
        private readonly FilterCollection $filterCollection,
        private readonly string           $resourcesDir,
    ) {}

    public function configure(): void
    {
        if ($this->isConfigured) {
            return;
        }

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
                        fn (string $content): string => self::preprocessIndex(
                            $content,
                            $basePath,
                            $apiBasePath,
                            $this->configuration->get("admin.misc.title.app_name", "Fundamenta"),
                        ),
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

        $this->isConfigured = true;
    }

    protected static function preprocessIndex(
        string $content,
        string $basePath,
        string $apiBasePath,
        string $appTitle,
    ): string
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
        $content = str_replace("%TITLE_APP_NAME%", $appTitle, $content);

        return $content;
    }
}
