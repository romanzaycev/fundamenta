<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Extensions\Tooolooop;

use Romanzaycev\Fundamenta\Components\Views\EngineManager;
use Romanzaycev\Fundamenta\Components\Views\Exceptions\EngineManagerException;
use Romanzaycev\Fundamenta\Components\Views\Exceptions\RenderingException;
use Romanzaycev\Fundamenta\Components\Views\ViewEngine;
use Romanzaycev\Fundamenta\Components\Views\ViewEngineProvider;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Tooolooop\Engine;

readonly class TooolooopViewEngine implements ViewEngine, ViewEngineProvider
{
    private string $extRegex;
    private string $ext;

    public function __construct(
        private Engine $engine,
        Configuration $configuration,
    )
    {
        $this->ext = $configuration->get("tooolooop.extension");
        $this->extRegex = "/" . str_replace(".", "\.", $this->ext) . "$/";
    }

    public function render(string $templatePath, array $data): string
    {
        try {
            $templatePath = preg_replace(
                $this->extRegex,
                "",
                $templatePath,
            );

            return $this
                ->engine
                ->make($templatePath)
                ->render($data);
        } catch (\Throwable $e) {
            throw new RenderingException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws EngineManagerException
     */
    public function register(EngineManager $manager): void
    {
        $manager->register(
            $this->ext,
            $this,
        );
    }
}
