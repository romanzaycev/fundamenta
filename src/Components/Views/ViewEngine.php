<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Views;

use Romanzaycev\Fundamenta\Components\Views\Exceptions\RenderingException;

interface ViewEngine
{
    /**
     * @throws RenderingException
     */
    public function render(string $templatePath, array $data): string;
}
