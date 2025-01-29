<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav;

final class AttributeHelper
{
    /** @var string[] */
    public const ENTITY_CODES = [
        "id",
        "alias",
        "created_at",
        "updated_at",
    ];

    public static function normalizeAttributeCode(string $code): string
    {
        return mb_strtolower(trim(preg_replace("/[^a-zA-Z0-9_-]/", "", $code)));
    }

    public static function isEntityOwned(string $code): bool
    {
        if (\str_starts_with($code, "ee.")) {
            $code = str_replace("ee.", "", $code);
        }

        return in_array($code, self::ENTITY_CODES, true);
    }
}
