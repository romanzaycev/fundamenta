<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Infrastructure\Pgsql;

final class Helper
{
    public static function convertDbDateToNative(string $dbDate): ?\DateTimeInterface
    {
        if (str_contains($dbDate, ".")) {
            $tmp = explode(".", $dbDate);
            $dbDate = $tmp[0];
        }

        if ($time = strtotime($dbDate)) {
            if ($dt = \DateTimeImmutable::createFromFormat("U", (string)$time)) {
                return $dt;
            }
        }

        return null;
    }

    public static function convertBool(mixed $bval): bool
    {
        return $bval === "true" || $bval === true || $bval === 1 || $bval === "1";
    }
}
