<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Startup;

interface ModuleManager
{
    /**
     * @param class-string<Bootstrapper> $bootstrapperClass
     */
    public function isModuleEnabled(string $bootstrapperClass): bool;
}
