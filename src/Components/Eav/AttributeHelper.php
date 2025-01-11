<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav;

final class AttributeHelper
{
    /** @var string[] */
    public const DENIED_CODES = [
        "id",
        "created_at",
        "updated_at",
        "type",
    ];

    public static function normalizeAttributeCode(string $code): string
    {
        return mb_strtolower(trim(preg_replace("/[^a-zA-Z0-9]/", "", $code)));
    }

    public static function isDeniedCode(string $code): bool
    {
        $code = self::normalizeAttributeCode($code);

        return in_array($code, self::DENIED_CODES, true);
    }
}
