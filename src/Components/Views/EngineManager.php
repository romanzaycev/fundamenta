<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Views;

use Romanzaycev\Fundamenta\Components\Views\Exceptions\EngineManagerException;

interface EngineManager
{
    /**
     * @throws EngineManagerException
     */
    public function register(string $fileExtension, ViewEngine $engine): void;

    public function getEngine(string $fileExtension): ViewEngine;
}
