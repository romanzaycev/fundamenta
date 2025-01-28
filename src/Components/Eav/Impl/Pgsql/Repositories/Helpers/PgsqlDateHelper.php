<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql\Repositories\Helpers;

use Romanzaycev\Fundamenta\Infrastructure\Pgsql\Helper;

final class PgsqlDateHelper
{
    public static function toNative(string $dbDate): ?\DateTimeInterface
    {
        return Helper::convertDbDateToNative($dbDate);
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
