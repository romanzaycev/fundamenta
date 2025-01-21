<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Startup;

class DefaultModuleManager implements ModuleManager
{
    /**
     * @var class-string<Bootstrapper>[]
     */
    private array $enabled = [];

    public function isModuleEnabled(string $bootstrapperClass): bool
    {
        return in_array($bootstrapperClass, $this->enabled, true);
    }

    /**
     * @param class-string<Bootstrapper> $moduleBootstrapperClass
     */
    public function markAsEnabled(string $moduleBootstrapperClass): void
    {
        if (!in_array($moduleBootstrapperClass, $this->enabled, true)) {
            $this->enabled[] = $moduleBootstrapperClass;
        }
    }
}
