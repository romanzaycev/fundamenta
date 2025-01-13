<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Http\Static;

interface InternalStaticFileInterface
{
    public function getPublicFile(): string;
    public function getRealFile(): string;
}
