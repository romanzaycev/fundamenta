<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Helpers;

use HaydenPierce\ClassFinder\ClassFinder as HaydenPierceClassFinder;

class ClassFinder
{
    public function __construct(
        protected readonly string $directory,
        protected readonly string $namespace,
    ) {}

    /**
     * @return class-string[]
     * @throws \Throwable
     */
    public function find(?ClassFinderFilter $filter = null): array
    {
        $classes = self::ensureClasses($this->namespace, $this->directory);

        if ($filter) {
            if ($subclassOf = $filter->getSubclassOf()) {
                $classes = array_filter(
                    $classes,
                    static function (string $class) use ($subclassOf) {
                        return is_subclass_of($class, $subclassOf);
                    },
                );
            }
        }

        return $classes;
    }

    /**
     * @return class-string[]
     * @throws \Throwable
     */
    protected static function ensureClasses(string $namespace, string $directory): array
    {
        HaydenPierceClassFinder::disablePSR4Vendors();

        $directory = rtrim($directory, "/\\");
        $directory = $directory . DIRECTORY_SEPARATOR;

        HaydenPierceClassFinder::setAppRoot($directory);

        return HaydenPierceClassFinder::getClassesInNamespace(
            $namespace,
            HaydenPierceClassFinder::RECURSIVE_MODE,
        );
    }
}
