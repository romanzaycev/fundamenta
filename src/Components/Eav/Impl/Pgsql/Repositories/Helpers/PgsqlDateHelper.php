<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql\Repositories\Helpers;

final class PgsqlDateHelper
{
    public static function toNative(string $dbDate): ?\DateTimeInterface
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

    public static function fromNativeAttribute(\DateTimeInterface $date): string
    {
        return $date->format(DATE_ATOM);
    }

    public static function toNativeAttribute(string $dbDate): ?\DateTimeInterface
    {
        if ($tmp = \DateTimeImmutable::createFromFormat(DATE_ATOM, $dbDate)) {
            return $tmp;
        }

        return null;
    }
}
