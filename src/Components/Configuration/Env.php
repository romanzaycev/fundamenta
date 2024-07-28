<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Configuration;

class Env
{
    public static function getBool(string $variable, bool $default): bool
    {
        $value = self::get($variable, $default);

        return $value === "1" || $value === "true" || $value === true;
    }

    public static function getString(string $variable, string $default): string
    {
        return self::get($variable, $default);
    }

    public static function getInt(string $variable, int $default): int
    {
        return (int)self::get($variable, $default);
    }

    protected static function get(string $variable, mixed $default): mixed
    {
        return $_ENV[strtoupper($variable)] ?? $default;
    }
}
