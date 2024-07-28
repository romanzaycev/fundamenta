<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Extensions\Tooolooop;

use Romanzaycev\Fundamenta\Components\Views\Exceptions\RenderingException;
use Romanzaycev\Fundamenta\Components\Views\ViewEngine;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Tooolooop\Engine;

readonly class TooolooopViewEngine implements ViewEngine
{
    private string $extRegex;

    public function __construct(
        private Engine $engine,
        Configuration $configuration,
    )
    {
        $this->extRegex = "/" . str_replace(".", "\.", $configuration->get("tooolooop.extension")) . "$/";
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
}
