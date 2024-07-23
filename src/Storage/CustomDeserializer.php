<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Storage;

interface CustomDeserializer
{
    public static function deserialize(string $data);
}
