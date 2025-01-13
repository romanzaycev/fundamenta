<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Http\Static;

interface InternalStaticDirectoryInterface
{
    public function getPublicDirectory(): string;
    public function getRealDirectory(): string;
}
