<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Views;

use Psr\Http\Message\StreamInterface;
use Romanzaycev\Fundamenta\Components\Views\Exceptions\RenderingException;

interface View
{
    /**
     * @throws RenderingException
     */
    public function render(string $templatePath, array $data, array $options = []): string;

    /**
     * @throws RenderingException
     */
    public function renderStream(string $templatePath, array $data, array $options = []): StreamInterface;
}
