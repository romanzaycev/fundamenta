<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Views\Engines;

use Romanzaycev\Fundamenta\Components\Views\ViewEngine;

class NullEngine implements ViewEngine
{
    public function render(string $templatePath, array $data): string
    {
        return "";
    }
}
