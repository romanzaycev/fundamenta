<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Helpers;

use Romanzaycev\Fundamenta\Configuration;

final class FrameworkClassFinder extends ClassFinder
{
    private static ?array $classCache = null;

    public static function create(Configuration $configuration): self
    {
        return new self(
            $configuration->get("app.path"),
            FrameworkHelper::getNs(),
        );
    }

    protected static function ensureClasses(string $namespace, string $directory): array
    {
        if (self::$classCache === null) {
            self::$classCache = parent::ensureClasses($namespace, $directory);
        }

        return self::$classCache;
    }
}
